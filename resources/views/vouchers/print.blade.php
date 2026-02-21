{{-- resources/views/vouchers/print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Print Vouchers</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Courier New', monospace; background:#f5f5f5; }
    .page-header { text-align:center; padding:16px; border-bottom:2px solid #333; margin-bottom:20px; }
    .page-header h1 { font-size:20px; }
    .grid { display:flex; flex-wrap:wrap; gap:12px; padding:12px; justify-content:flex-start; }
    .voucher {
        width:calc(33.33% - 8px);
        border:2px dashed #0d6efd;
        border-radius:10px;
        padding:14px;
        background:#fff;
        page-break-inside:avoid;
    }
    .voucher-header { font-size:8px; text-transform:uppercase; color:#6b7280; letter-spacing:1px; margin-bottom:6px; }
    .voucher-username { font-size:18px; font-weight:700; color:#0d6efd; letter-spacing:2px; margin-bottom:4px; }
    .voucher-password { font-size:14px; color:#374151; border:1px solid #e5e7eb; background:#f9fafb; padding:4px 8px; border-radius:4px; display:inline-block; margin-bottom:8px; }
    .voucher-meta { display:flex; justify-content:space-between; border-top:1px dashed #e5e7eb; padding-top:6px; margin-top:6px; }
    .voucher-meta-item { text-align:center; }
    .voucher-meta-item .label { font-size:7px; color:#9ca3af; text-transform:uppercase; display:block; }
    .voucher-meta-item .value { font-size:11px; font-weight:700; color:#1f2937; }
    .wifi-icon { text-align:center; color:#0d6efd; font-size:20px; margin-bottom:4px; }
    @media print {
        body { background:#fff; }
        .no-print { display:none; }
        .page-header { margin-bottom:12px; }
    }
</style>
</head>
<body>

<div class="no-print" style="background:#0d6efd;color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between">
    <span style="font-weight:700">Print Vouchers — {{ $vouchers->count() }} vouchers</span>
    <button onclick="window.print()" style="background:#fff;color:#0d6efd;border:none;padding:7px 18px;border-radius:5px;font-weight:700;cursor:pointer">
        🖨 Print Now
    </button>
</div>

<div class="page-header no-print">
    <h1>Hotspot Vouchers</h1>
    <p style="font-size:12px;color:#6b7280">Generated {{ now()->format('d M Y H:i') }}</p>
</div>

<div class="grid">
    @foreach($vouchers as $v)
    <div class="voucher">
        <div class="wifi-icon">📶</div>
        <div class="voucher-header">Hotspot Voucher</div>
        <div class="voucher-username">{{ $v->username }}</div>
        <div style="font-size:9px;color:#9ca3af;margin-bottom:4px">Password:</div>
        <div class="voucher-password">{{ $v->password }}</div>
        <div class="voucher-meta">
            <div class="voucher-meta-item">
                <span class="label">Profile</span>
                <span class="value">{{ $v->profile }}</span>
            </div>
            <div class="voucher-meta-item">
                <span class="label">Time</span>
                <span class="value">{{ $v->time_limit_formatted }}</span>
            </div>
            <div class="voucher-meta-item">
                <span class="label">Price</span>
                <span class="value">{{ $v->price ? '$'.number_format($v->price,2) : 'Free' }}</span>
            </div>
        </div>
    </div>
    @endforeach
</div>
</body>
</html>
