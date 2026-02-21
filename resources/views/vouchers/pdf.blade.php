{{-- resources/views/vouchers/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Vouchers Export</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1a1a2e; }
    .header { background:#0d6efd; color:#fff; padding:16px 20px; margin-bottom:16px; border-radius:6px; }
    .header h1 { font-size:18px; font-weight:700; margin-bottom:2px; }
    .header p  { font-size:10px; opacity:0.8; }
    table { width:100%; border-collapse:collapse; margin-top:8px; }
    thead tr { background:#0d6efd; color:#fff; }
    thead th { padding:8px 10px; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px; }
    tbody tr:nth-child(even) { background:#f0f4ff; }
    tbody td { padding:7px 10px; border-bottom:1px solid #e2e8f0; }
    .badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:9px; font-weight:700; text-transform:uppercase; }
    .badge-success  { background:#d1fae5; color:#065f46; }
    .badge-secondary{ background:#e5e7eb; color:#374151; }
    .badge-danger   { background:#fee2e2; color:#991b1b; }
    .badge-warning  { background:#fef3c7; color:#92400e; }
    .footer { margin-top:16px; font-size:9px; color:#6b7280; text-align:center; border-top:1px solid #e2e8f0; padding-top:8px; }
</style>
</head>
<body>
<div class="header">
    <h1>Vouchers Export</h1>
    <p>Generated: {{ now()->format('d M Y H:i:s') }} · Total: {{ $vouchers->count() }} vouchers</p>
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Username</th>
            <th>Password</th>
            <th>Profile</th>
            <th>Status</th>
            <th>Time</th>
            <th>Data</th>
            <th>Price</th>
            <th>Batch</th>
            <th>Synced</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vouchers as $v)
        <tr>
            <td>{{ $v->id }}</td>
            <td><strong>{{ $v->username }}</strong></td>
            <td>{{ $v->password }}</td>
            <td>{{ $v->profile }}</td>
            <td><span class="badge badge-{{ $v->status_badge }}">{{ $v->status }}</span></td>
            <td>{{ $v->time_limit_formatted }}</td>
            <td>{{ $v->data_limit_formatted }}</td>
            <td>{{ $v->price ? '$'.number_format($v->price,2) : '—' }}</td>
            <td>{{ $v->batch ?: '—' }}</td>
            <td>{{ $v->mikrotik_synced ? 'Yes' : 'No' }}</td>
            <td>{{ $v->created_at->format('d/m/Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">MikroTik Hotspot Manager · Vouchers Report · {{ now()->format('Y') }}</div>
</body>
</html>
