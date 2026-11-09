<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePackage;
use App\Models\Order;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->orders()->latest()->paginate(20));
    }

    public function show(Request $request, int $order): JsonResponse
    {
        return response()->json(['data' => $request->user()->orders()->findOrFail($order)]);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', 'max:60'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $orders = Order::with('user:id,name,email')
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate($data['per_page'] ?? 20)
            ->withQueryString();

        return response()->json($orders);
    }

    public function adminShow(int $order): JsonResponse
    {
        return response()->json(['data' => Order::with('user:id,name,email')->findOrFail($order)]);
    }

    public function adminUpdate(Request $request, int $order, AuditLogger $auditLogger): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Menunggu pembayaran', 'Diproses', 'Lunas', 'Dibatalkan'])],
        ]);
        $order = Order::findOrFail($order);
        $previousStatus = $order->status;
        $order->update(['status' => $data['status']]);

        if ($previousStatus !== $order->status) {
            $auditLogger->record($request, 'order.status_updated', $order, [
                'from' => $previousStatus,
                'to' => $order->status,
            ]);
        }

        return response()->json(['data' => $order->refresh()->load('user:id,name,email')]);
    }

    public function store(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $data = $request->validate([
            'game_slug' => ['required', 'string', 'exists:games,slug'],
            'package_id' => ['required', 'integer', 'exists:game_packages,id'],
            'player_id' => ['required', 'string', 'max:80'],
            'zone_id' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'in:QRIS,GoPay,Transfer Bank,DANA'],
        ]);

        $order = DB::transaction(function () use ($request, $data) {
            $game = Game::where('slug', $data['game_slug'])->where('active', true)->firstOrFail();
            $package = GamePackage::where('game_id', $game->id)
                ->whereKey($data['package_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($package->stock < 1) {
                throw ValidationException::withMessages([
                    'package_id' => ['Stok opsi game sedang habis.'],
                ]);
            }

            $playerId = $data['player_id'];
            if (! empty($data['zone_id'])) {
                if (! $game->region) {
                    throw ValidationException::withMessages([
                        'zone_id' => ['Game ini tidak menggunakan zone/server ID.'],
                    ]);
                }
                $playerId .= ' ('.$data['zone_id'].')';
            } elseif ($game->region) {
                throw ValidationException::withMessages([
                    'zone_id' => ['Zone/server ID wajib untuk game ini.'],
                ]);
            }

            $package->decrement('stock');

            return $request->user()->orders()->create([
                'game' => $game->name,
                'package' => $package->name,
                'player_id' => $playerId,
                'amount' => $package->price,
                'payment_method' => $data['payment_method'],
                'status' => 'Menunggu pembayaran',
            ]);
        });

        $auditLogger->record($request, 'order.created', $order, [
            'status' => $order->status,
            'amount' => $order->amount,
        ]);

        return response()->json(['data' => $order], 201);
    }
}
