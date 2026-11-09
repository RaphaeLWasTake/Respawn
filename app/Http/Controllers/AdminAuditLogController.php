<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:10', 'max:100'],
        ]);

        $logs = AuditLog::query()
            ->when($data['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->orderByDesc('id')
            ->paginate($data['per_page'] ?? 25)
            ->withQueryString();
        $actions = AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');

        return view('dashboard.audit-logs', compact('logs', 'actions'));
    }
}
