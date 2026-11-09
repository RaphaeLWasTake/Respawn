<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $logs = AuditLog::query()
            ->when($data['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->orderByDesc('id')
            ->paginate($data['per_page'] ?? 20)
            ->withQueryString();

        return response()->json($logs);
    }
}
