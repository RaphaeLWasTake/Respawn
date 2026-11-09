<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Game;
use App\Models\GamePackage;
use Illuminate\Validation\ValidationException;
use App\Services\AuditLogger;

class StoreController extends Controller
{
    private const GAMES = [
        'genshin-impact' => ['name' => 'Genshin Impact', 'genre' => 'Open-world RPG', 'icon' => '✦', 'image' => 'images/games/genshin-impact.jpg', 'tag' => 'HoYoverse', 'color' => 'violet', 'currency' => 'Genesis Crystal', 'id_label' => 'UID', 'region' => true, 'packages' => [['name' => '60 Genesis Crystal', 'amount' => 60, 'price' => 15000], ['name' => 'Blessing of the Welkin Moon', 'amount' => 300, 'price' => 79000], ['name' => '300 + 30 Genesis Crystal', 'amount' => 330, 'price' => 79000], ['name' => '980 + 110 Genesis Crystal', 'amount' => 1090, 'price' => 239000], ['name' => '1980 + 260 Genesis Crystal', 'amount' => 2240, 'price' => 479000], ['name' => '3280 + 600 Genesis Crystal', 'amount' => 3880, 'price' => 799000]]],
        'honkai-star-rail' => ['name' => 'Honkai: Star Rail', 'genre' => 'Fantasy RPG', 'icon' => '★', 'image' => 'images/games/honkai-star-rail.png', 'tag' => 'HoYoverse', 'color' => 'blue', 'currency' => 'Oneiric Shard', 'id_label' => 'UID', 'region' => true, 'packages' => [['name' => '60 Oneiric Shard', 'amount' => 60, 'price' => 15000], ['name' => 'Express Supply Pass', 'amount' => 300, 'price' => 79000], ['name' => '300 + 30 Oneiric Shard', 'amount' => 330, 'price' => 79000], ['name' => '980 + 110 Oneiric Shard', 'amount' => 1090, 'price' => 239000], ['name' => '1980 + 260 Oneiric Shard', 'amount' => 2240, 'price' => 479000], ['name' => '3280 + 600 Oneiric Shard', 'amount' => 3880, 'price' => 799000]]],
        'pubg-mobile' => ['name' => 'PUBG Mobile', 'genre' => 'Battle Royale', 'icon' => 'P', 'image' => 'images/games/pubg-mobile.jpg', 'tag' => 'KRAFTON', 'color' => 'amber', 'currency' => 'UC', 'id_label' => 'Player ID', 'region' => false, 'packages' => [['name' => '60 UC', 'amount' => 60, 'price' => 15000], ['name' => '325 UC', 'amount' => 325, 'price' => 79000], ['name' => '660 UC', 'amount' => 660, 'price' => 159000], ['name' => '1800 UC', 'amount' => 1800, 'price' => 399000], ['name' => '3850 UC', 'amount' => 3850, 'price' => 799000], ['name' => '8100 UC', 'amount' => 8100, 'price' => 1599000]]],
        'free-fire' => ['name' => 'Free Fire', 'genre' => 'Battle Royale', 'icon' => '🔥', 'image' => 'images/games/free-fire.png', 'tag' => 'Garena', 'color' => 'orange', 'currency' => 'Diamond', 'id_label' => 'Player ID', 'region' => false, 'packages' => [['name' => '5 Diamond', 'amount' => 5, 'price' => 1000], ['name' => '50 Diamond', 'amount' => 50, 'price' => 8000], ['name' => '70 Diamond', 'amount' => 70, 'price' => 10000], ['name' => '140 Diamond', 'amount' => 140, 'price' => 20000], ['name' => '355 Diamond', 'amount' => 355, 'price' => 50000], ['name' => '720 Diamond', 'amount' => 720, 'price' => 100000], ['name' => '1450 Diamond', 'amount' => 1450, 'price' => 200000]]],
        'mobile-legends' => ['name' => 'Mobile Legends: Bang Bang', 'genre' => 'MOBA', 'icon' => 'ML', 'image' => 'images/games/mobile-legends.jpg', 'tag' => 'MOONTON', 'color' => 'cyan', 'currency' => 'Diamond', 'id_label' => 'User ID', 'region' => true, 'packages' => [['name' => '86 Diamond', 'amount' => 86, 'price' => 20000], ['name' => '172 Diamond', 'amount' => 172, 'price' => 39000], ['name' => '257 Diamond', 'amount' => 257, 'price' => 59000], ['name' => '344 Diamond', 'amount' => 344, 'price' => 79000], ['name' => 'Weekly Diamond Pass', 'amount' => 220, 'price' => 30000], ['name' => '706 Diamond', 'amount' => 706, 'price' => 159000], ['name' => '1412 Diamond', 'amount' => 1412, 'price' => 319000]]],
        'magic-chess-go-go' => ['name' => 'Magic Chess: Go Go', 'genre' => 'Strategy', 'icon' => '♟', 'image' => 'images/games/magic-chess.jpg', 'tag' => 'MOONTON', 'color' => 'pink', 'currency' => 'Diamond', 'id_label' => 'User ID', 'region' => true, 'packages' => [['name' => '56 Diamond', 'amount' => 56, 'price' => 15000], ['name' => '112 Diamond', 'amount' => 112, 'price' => 30000], ['name' => '168 Diamond', 'amount' => 168, 'price' => 45000], ['name' => '280 Diamond', 'amount' => 280, 'price' => 75000], ['name' => '568 Diamond', 'amount' => 568, 'price' => 149000], ['name' => '1160 Diamond', 'amount' => 1160, 'price' => 299000]]],
    ];

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        DB::table('visitor_logs')->updateOrInsert(
            ['visitor_key' => hash('sha256', $request->session()->getId()), 'visited_on' => now()->toDateString()],
            ['updated_at' => now(), 'created_at' => now()]
        );

        if ($user->role === 'admin') {
            $orders = DB::table('orders')->latest()->limit(8)->get();
            $visitors = DB::table('visitor_logs')->whereDate('visited_on', today())->count();
            $paid = DB::table('orders')->where('status', 'Lunas')->count();
            $pending = DB::table('orders')->where('status', 'Menunggu pembayaran')->count();
            $stock = Game::with('packages')->get()->mapWithKeys(fn (Game $game) => [$game->slug => [
                'name' => $game->name,
                'quantity' => $game->packages->sum('stock'),
            ]]);

            return view('dashboard.admin', compact('orders', 'visitors', 'paid', 'pending', 'stock'));
        }

        if ($user->role === 'helper') {
            $tickets = DB::table('support_tickets')->join('users', 'users.id', '=', 'support_tickets.user_id')
                ->select('support_tickets.*', 'users.username', 'users.name')->latest('support_tickets.created_at')->get();

            return view('dashboard.helper', compact('tickets'));
        }

        return view('dashboard.user', ['games' => self::GAMES]);
    }

    public function game(string $slug): View
    {
        $games = self::games();
        abort_unless(isset($games[$slug]), 404);

        return view('games.show', ['game' => $games[$slug], 'slug' => $slug]);
    }

    public function order(Request $request, string $slug, AuditLogger $auditLogger): RedirectResponse
    {
        $games = self::games();
        abort_unless(isset($games[$slug]), 404);
        $game = $games[$slug];
        $data = $request->validate([
            'player_id' => ['required', 'string', 'max:80'],
            'zone_id' => [$game['region'] ? 'required' : 'nullable', 'string', 'max:30'],
            'package' => ['required', 'integer', 'min:0', 'max:'.(count($game['packages']) - 1)],
            'payment_method' => ['required', 'in:QRIS,GoPay,Transfer Bank,DANA'],
        ]);

        $package = $game['packages'][$data['package']];
        $zoneId = $data['zone_id'] ?? null;
        $playerId = $data['player_id'].($zoneId ? ' ('.$zoneId.')' : '');
        $order = DB::transaction(function () use ($request, $slug, $package, $playerId, $data) {
            $gameModel = Game::where('slug', $slug)->firstOrFail();
            $packageModel = GamePackage::where('game_id', $gameModel->id)
                ->whereKey($package['id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($packageModel->stock < 1) {
                throw ValidationException::withMessages([
                    'package' => ['Stok opsi game sedang habis.'],
                ]);
            }

            $packageModel->decrement('stock');

            return $request->user()->orders()->create([
                'game' => $gameModel->name,
                'package' => $packageModel->name,
                'player_id' => $playerId,
                'amount' => $packageModel->price,
                'payment_method' => $data['payment_method'],
                'status' => 'Menunggu pembayaran',
            ]);
        });

        $auditLogger->record($request, 'order.created', $order, [
            'status' => $order->status,
            'amount' => $order->amount,
        ]);

        return redirect()->route('game.show', $slug)->with('success', 'Pesanan #'.$order->id.' berhasil dibuat. Selesaikan pembayaran '.$package['name'].' sebesar Rp '.number_format($package['price'], 0, ',', '.').'.');
    }

    public function support(): View
    {
        $tickets = auth()->user()->supportTickets()->latest()->get();

        return view('support.index', compact('tickets'));
    }

    public function createTicket(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);
        $request->user()->supportTickets()->create($data);

        return back()->with('success', 'Pesan terkirim. Tim CS akan segera membantu kamu.');
    }

    public function reply(Request $request, int $ticket): RedirectResponse
    {
        $data = $request->validate(['reply' => ['required', 'string', 'max:2000']]);
        DB::table('support_tickets')->where('id', $ticket)->update([
            'reply' => $data['reply'],
            'status' => 'Sudah dibalas',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public static function games(): array
    {
        return Game::query()
            ->where('active', true)
            ->with('packages')
            ->get()
            ->mapWithKeys(fn (Game $game) => [$game->slug => [
                'name' => $game->name,
                'genre' => $game->genre,
                'icon' => $game->icon,
                'image' => $game->image,
                'tag' => $game->tag,
                'color' => $game->color,
                'currency' => $game->currency,
                'id_label' => $game->id_label,
                'region' => $game->region,
                'packages' => $game->packages->map(fn ($package) => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'amount' => $package->amount,
                    'price' => $package->price,
                    'stock' => $package->stock,
                ])->all(),
            ]])
            ->all();
    }

    public static function defaultGames(): array
    {
        return self::GAMES;
    }
}
