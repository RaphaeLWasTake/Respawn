<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    private const STATUSES = ['Menunggu balasan', 'Sedang diproses', 'Sudah dibalas', 'Selesai'];

    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->supportTickets()->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $ticket = $request->user()->supportTickets()->create($request->validate([
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]));

        return response()->json(['data' => $ticket], 201);
    }

    public function helperIndex(): JsonResponse
    {
        return response()->json(
            SupportTicket::with('user:id,name,username,email')->latest()->paginate(20)
        );
    }

    public function helperUpdate(Request $request, SupportTicket $ticket): JsonResponse
    {
        if (! $request->hasAny(['reply', 'status'])) {
            throw ValidationException::withMessages([
                'ticket' => ['Kirim reply dan/atau status untuk mengubah tiket.'],
            ]);
        }

        $data = $request->validate([
            'reply' => ['sometimes', 'required', 'string', 'max:2000'],
            'status' => ['sometimes', 'required', Rule::in(self::STATUSES)],
        ]);
        $ticket->update($data);

        return response()->json(['data' => $ticket->refresh()->load('user:id,name,username,email')]);
    }
}
