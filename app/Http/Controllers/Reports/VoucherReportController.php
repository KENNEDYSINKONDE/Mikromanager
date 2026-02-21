<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Services\RouterSession;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VoucherReportController extends Controller
{
    public function index(Request $request)
    {
        $routerId = RouterSession::id();
        
        // Date range filter
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate   = $request->input('end_date', now()->endOfMonth());

        // Base query
        $query = Voucher::where('router_id', $routerId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Overall stats
        $stats = [
            'total'       => (clone $query)->count(),
            'active'      => (clone $query)->where('status', 'active')->count(),
            'used'        => (clone $query)->where('status', 'used')->count(),
            'expired'     => (clone $query)->where('status', 'expired')->count(),
            'disabled'    => (clone $query)->where('status', 'disabled')->count(),
            
            // Revenue
            'total_revenue'   => (clone $query)->sum('price') ?? 0,
            'earned_revenue'  => (clone $query)->whereIn('status', ['used', 'expired'])->sum('price') ?? 0,
            'pending_revenue' => (clone $query)->where('status', 'active')->sum('price') ?? 0,
            
            // Usage
            'total_bytes_in'  => (clone $query)->sum('bytes_in'),
            'total_bytes_out' => (clone $query)->sum('bytes_out'),
            'total_session_time' => (clone $query)->sum('session_time'),
        ];

        // Status breakdown by date
        $dailyStats = Voucher::where('router_id', $routerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count, SUM(price) as revenue')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Top batches by revenue
        $topBatches = Voucher::where('router_id', $routerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('batch')
            ->selectRaw('batch, COUNT(*) as total, 
                         SUM(CASE WHEN status IN ("used","expired") THEN 1 ELSE 0 END) as used_count,
                         SUM(CASE WHEN status IN ("used","expired") THEN price ELSE 0 END) as earned')
            ->groupBy('batch')
            ->orderByDesc('earned')
            ->limit(10)
            ->get();

        return view('reports.vouchers', compact('stats', 'dailyStats', 'topBatches', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        $routerId = RouterSession::id();
        
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate   = $request->input('end_date', now()->endOfMonth());

        $query = Voucher::where('router_id', $routerId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $stats = [
            'total'          => (clone $query)->count(),
            'active'         => (clone $query)->where('status', 'active')->count(),
            'used'           => (clone $query)->where('status', 'used')->count(),
            'expired'        => (clone $query)->where('status', 'expired')->count(),
            'total_revenue'  => (clone $query)->sum('price') ?? 0,
            'earned_revenue' => (clone $query)->whereIn('status', ['used', 'expired'])->sum('price') ?? 0,
        ];

        $batches = Voucher::where('router_id', $routerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('batch')
            ->selectRaw('batch, COUNT(*) as total, 
                         SUM(CASE WHEN status="used" OR status="expired" THEN 1 ELSE 0 END) as used,
                         SUM(CASE WHEN status="used" OR status="expired" THEN price ELSE 0 END) as revenue')
            ->groupBy('batch')
            ->orderByDesc('revenue')
            ->get();

        $router = RouterSession::router();

        $pdf = Pdf::loadView('reports.vouchers-pdf', compact('stats', 'batches', 'startDate', 'endDate', 'router'));
        
        return $pdf->download('voucher-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $routerId = RouterSession::id();
        
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate   = $request->input('end_date', now()->endOfMonth());

        $vouchers = Voucher::where('router_id', $routerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $filename = 'voucher-report-' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($vouchers) {
            $h = fopen('php://output', 'w');
            
            // Header row
            fputcsv($h, [
                'Date Created',
                'Username',
                'Profile',
                'Status',
                'Price',
                'Batch',
                'First Used',
                'Session Time',
                'Data In',
                'Data Out',
                'Total Data',
            ]);

            foreach ($vouchers as $v) {
                fputcsv($h, [
                    $v->created_at->format('Y-m-d H:i'),
                    $v->username,
                    $v->profile,
                    ucfirst($v->status),
                    $v->price ?? 0,
                    $v->batch ?? '',
                    $v->first_used_at?->format('Y-m-d H:i') ?? '—',
                    $v->session_time_formatted,
                    $v->bytes_in_formatted,
                    $v->bytes_out_formatted,
                    $v->total_bytes_formatted,
                ]);
            }
            
            fclose($h);
        }, 200, $headers);
    }
}
