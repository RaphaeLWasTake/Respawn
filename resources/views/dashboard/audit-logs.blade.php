@extends('layouts.app')
@section('title', 'Riwayat audit — Respawn')
@section('content')
<style>
    .audit-panel .panel-heading{display:flex;align-items:end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:12px}
    .audit-filter{display:flex;align-items:end;gap:8px;flex-wrap:wrap}
    .audit-filter label{display:grid;gap:5px;color:#a8a8b2;font-size:9px}
    .audit-filter select{min-height:36px;padding:0 10px;border:1px solid #35353c;border-radius:6px;background:#18181c;color:#eee;font-size:10px}
    .audit-panel td:nth-child(5){white-space:normal;min-width:135px}
    .audit-panel details summary{color:#c8fa62;cursor:pointer;font-size:9px}
    .audit-panel pre{max-width:320px;max-height:180px;overflow:auto;white-space:pre-wrap;color:#c4c4cb;font-size:9px}
    .audit-pagination{padding-top:16px}
    @media(max-width:600px){.audit-filter{width:100%}.audit-filter select{max-width:150px}.audit-panel .panel-heading{align-items:flex-start}}
</style>
<section class="dashboard-shell">
    <div class="admin-head">
        <div>
            <div class="eyebrow"><span class="spark">✳</span> ADMIN / AUDIT TRAIL</div>
            <h1>Riwayat <span class="muted-heading">audit.</span></h1>
            <p>Aktivitas penting pada order dan akun tercatat di sini.</p>
        </div>
        <a class="button button-outline button-small" href="{{ route('dashboard') }}">Kembali ke dashboard</a>
    </div>

    <div class="admin-panel audit-panel">
        <div class="panel-heading">
            <div>
                <div class="eyebrow">CATATAN AKTIVITAS</div>
                <h2>{{ number_format($logs->total()) }} catatan</h2>
            </div>
            <form method="GET" action="{{ route('admin.audit-logs') }}" class="audit-filter">
                <label for="action-filter">Aktivitas</label>
                <select id="action-filter" name="action">
                    <option value="">Semua aktivitas</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
                <label for="per-page-filter">Baris</label>
                <select id="per-page-filter" name="per_page">
                    @foreach([25, 50, 100] as $perPage)
                        <option value="{{ $perPage }}" @selected((int) request('per_page', 25) === $perPage)>{{ $perPage }}</option>
                    @endforeach
                </select>
                <button class="button button-primary button-small" type="submit">Terapkan</button>
                @if(request()->hasAny(['action', 'per_page']))
                    <a class="button button-outline button-small" href="{{ route('admin.audit-logs') }}">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>WAKTU</th>
                        <th>AKTOR</th>
                        <th>AKTIVITAS</th>
                        <th>OBJEK</th>
                        <th>PERUBAHAN</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d M Y, H:i:s') }}</time></td>
                            <td>{{ $log->actor_user_id ? '#'.$log->actor_user_id : 'System' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td>
                                @if($log->changes)
                                    <details>
                                        <summary>Lihat detail</summary>
                                        <pre>{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    <span class="muted-heading">—</span>
                                @endif
                            </td>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Belum ada aktivitas yang tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="audit-pagination">{{ $logs->links() }}</div>
    </div>
</section>
@endsection
