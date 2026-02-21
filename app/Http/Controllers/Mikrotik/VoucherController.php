<?php

namespace App\Http\Controllers\Mikrotik;

use App\Models\Voucher;
use App\Http\Controllers\Controller;
use App\Services\VoucherMikroTikService;
use App\Services\RouterSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class VoucherController extends Controller
{
    private function mikrotik(): VoucherMikroTikService
    {
        return VoucherMikroTikService::fromSession();
    }

    private function routerId(): int
    {
        return RouterSession::id();
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $routerId = $this->routerId();
        $query    = Voucher::where('router_id', $routerId)->latest();

        if ($search  = $request->get('search'))  $query->search($search);
        if ($status  = $request->get('status'))  $query->where('status', $status);
        if ($profile = $request->get('profile')) $query->where('profile', $profile);
        if ($batch   = $request->get('batch'))   $query->where('batch', $batch);

        $vouchers = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => Voucher::where('router_id', $routerId)->count(),
            'active'   => Voucher::where('router_id', $routerId)->where('status', 'active')->count(),
            'used'     => Voucher::where('router_id', $routerId)->where('status', 'used')->count(),
            'expired'  => Voucher::where('router_id', $routerId)->where('status', 'expired')->count(),
            'disabled' => Voucher::where('router_id', $routerId)->where('status', 'disabled')->count(),
        ];

        try { $profiles = $this->mikrotik()->listProfiles(); } catch (\Exception) { $profiles = []; }
        $batches = Voucher::where('router_id', $routerId)->distinct()->whereNotNull('batch')->pluck('batch');

        return view('vouchers.index', compact('vouchers', 'stats', 'profiles', 'batches'));
    }

    // ── CREATE ────────────────────────────────────────────────────────────────
    public function create()
    {
        try { $profiles = $this->mikrotik()->listProfiles(); } catch (\Exception) { $profiles = []; }
        return view('vouchers.create', compact('profiles'));
    }

    // ── STORE ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'   => 'required|string|max:64|unique:vouchers,username',
            'password'   => 'required|string|max:64',
            'profile'    => 'required|string|max:64',
            'price'      => 'nullable|numeric|min:0',
            'batch'      => 'nullable|string|max:64',
            'note'       => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
        ]);

        // Ensure username != password when mode is 'different'
        if ($validated['username'] === $validated['password'] && $request->input('pw_mode') === 'different') {
            return back()->withInput()->with('error', 'Username and password cannot be the same when using separate codes.');
        }

        $voucher = Voucher::create([
            'router_id'       => $this->routerId(),
            'username'        => $validated['username'],
            'password'        => $validated['password'],
            'profile'         => $validated['profile'],
            'time_limit'      => null,   // comes from MikroTik profile
            'data_limit'      => null,   // comes from MikroTik profile
            'price'           => $validated['price'] ?? null,
            'batch'           => $validated['batch'] ?? null,
            'note'            => $validated['note'] ?? null,
            'expires_at'      => $validated['expires_at'] ?? null,
            'status'          => 'active',
            'mikrotik_synced' => false,
        ]);

        if ($request->boolean('push_now')) {
            try {
                $synced = $this->mikrotik()->pushVoucher(
                    username: $voucher->username, password: $voucher->password,
                    profile: $voucher->profile, timeLimit: $voucher->time_limit,
                    dataLimit: $voucher->data_limit, comment: 'Voucher #' . $voucher->id,
                );
                $voucher->update(['mikrotik_synced' => $synced]);
            } catch (\Exception $e) {
                session()->flash('warning', 'Voucher saved but MikroTik sync failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('vouchers.show', $voucher)
            ->with('success', "Voucher <strong>{$voucher->username}</strong> created successfully.");
    }

    // ── BULK GENERATE ─────────────────────────────────────────────────────────
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'count'       => 'required|integer|min:1|max:500',
            'profile'     => 'required|string|max:64',
            'prefix'      => 'nullable|string|max:20',
            'code_length' => 'required|integer|in:4,5,6,7,8,10,12',
            'code_type'   => 'required|in:numbers,letters_upper,letters_lower,mixed_upper,mixed_lower,mixed_both',
            'pw_mode'     => 'required|in:same,different',
            'price'       => 'nullable|numeric|min:0',
            'batch'       => 'required|string|max:64',
        ]);

        $routerId = $this->routerId();
        $prefix   = $validated['prefix'] ?? '';
        $batch    = $validated['batch'];
        $length   = (int) $validated['code_length'];
        $type     = $validated['code_type'];
        $pwMode   = $validated['pw_mode'];
        $created  = [];

        // Character sets — no ambiguous chars (0,O,1,l,I)
        $charsets = [
            'numbers'      => '23456789',
            'letters_upper'=> 'ABCDEFGHJKLMNPQRSTUVWXYZ',
            'letters_lower'=> 'abcdefghjkmnpqrstuvwxyz',
            'mixed_upper'  => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
            'mixed_lower'  => 'abcdefghjkmnpqrstuvwxyz23456789',
            'mixed_both'   => 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789',
        ];
        $charset = $charsets[$type];

        for ($i = 0; $i < $validated['count']; $i++) {
            // Generate unique username
            do {
                $code     = '';
                for ($c = 0; $c < $length; $c++) {
                    $code .= $charset[random_int(0, strlen($charset) - 1)];
                }
                $username = $prefix . $code;
            } while (Voucher::where('username', $username)->exists());

            // Generate password
            if ($pwMode === 'same') {
                $passwordVal = $username;
            } else {
                do {
                    $pwCode = '';
                    for ($c = 0; $c < $length; $c++) {
                        $pwCode .= $charset[random_int(0, strlen($charset) - 1)];
                    }
                    $passwordVal = $prefix . $pwCode;
                } while ($passwordVal === $username); // ensure not same
            }

            $created[] = Voucher::create([
                'router_id'       => $routerId,
                'username'        => $username,
                'password'        => $passwordVal,
                'profile'         => $validated['profile'],
                'time_limit'      => null,  // from MikroTik profile
                'data_limit'      => null,  // from MikroTik profile
                'price'           => $validated['price'] ?? null,
                'batch'           => $batch,
                'status'          => 'active',
                'mikrotik_synced' => false,
            ]);
        }

        if ($request->boolean('push_now')) {
            try {
                $payload = array_map(fn($v) => ['username' => $v->username, 'password' => $v->password, 'profile' => $v->profile, 'time_limit' => $v->time_limit, 'data_limit' => $v->data_limit, 'comment' => 'Batch: ' . $batch], $created);
                $synced  = $this->mikrotik()->pushVoucherBatch($payload);
                Voucher::where('router_id', $routerId)->where('batch', $batch)->update(['mikrotik_synced' => true]);
                return redirect()->route('vouchers.index', ['batch' => $batch])
                    ->with('success', "<strong>{$validated['count']}</strong> vouchers generated. <strong>{$synced}</strong> synced to MikroTik.");
            } catch (\Exception $e) {
                session()->flash('warning', 'Vouchers generated but MikroTik sync failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('vouchers.index', ['batch' => $batch])
            ->with('success', "<strong>{$validated['count']}</strong> vouchers generated in batch <strong>{$batch}</strong>.");
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────
    public function show(Voucher $voucher)
    {
        $this->authorizeVoucher($voucher);
        return view('vouchers.show', compact('voucher'));
    }

    // ── EDIT ──────────────────────────────────────────────────────────────────
    public function edit(Voucher $voucher)
    {
        $this->authorizeVoucher($voucher);
        try { $profiles = $this->mikrotik()->listProfiles(); } catch (\Exception) { $profiles = []; }
        return view('vouchers.edit', compact('voucher', 'profiles'));
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────
    public function update(Request $request, Voucher $voucher)
    {
        $this->authorizeVoucher($voucher);
        $validated = $request->validate([
            'profile'    => 'required|string|max:64',
            'status'     => 'required|in:active,used,expired,disabled',
            'time_limit' => 'nullable|integer|min:1',
            'data_limit' => 'nullable|integer|min:1',
            'price'      => 'nullable|numeric|min:0',
            'batch'      => 'nullable|string|max:64',
            'note'       => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
        ]);
        $voucher->update($validated);
        return redirect()->route('vouchers.show', $voucher)
            ->with('success', "Voucher <strong>{$voucher->username}</strong> updated successfully.");
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────
    public function destroy(Voucher $voucher)
    {
        $this->authorizeVoucher($voucher);
        $username = $voucher->username;

        if ($voucher->mikrotik_synced) {
            try { $this->mikrotik()->removeUser($username); } catch (\Exception) {}
        }

        $voucher->delete();
        return redirect()->route('vouchers.index')
            ->with('success', "Voucher <strong>{$username}</strong> deleted.");
    }

    // ── BULK ACTIONS ──────────────────────────────────────────────────────────
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,disable,enable,sync',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        // Only allow vouchers belonging to this router
        $vouchers = Voucher::whereIn('id', $request->ids)->where('router_id', $this->routerId())->get();
        $count    = $vouchers->count();

        foreach ($vouchers as $voucher) {
            match ($request->action) {
                'delete'  => $this->bulkDelete($voucher),
                'disable' => $this->bulkDisable($voucher),
                'enable'  => $this->bulkEnable($voucher),
                'sync'    => $this->bulkSync($voucher),
            };
        }

        $label = match($request->action) {
            'delete'  => 'deleted',
            'disable' => 'disabled',
            'enable'  => 'enabled',
            'sync'    => 'synced',
            default   => $request->action . 'd',
        };

        return redirect()->route('vouchers.index')
            ->with('success', "<strong>{$count}</strong> voucher(s) {$label} successfully.");
    }

    // ── SYNC SINGLE ───────────────────────────────────────────────────────────
    public function sync(Voucher $voucher)
    {
        $this->authorizeVoucher($voucher);
        try {
            $synced = $this->mikrotik()->pushVoucher(username: $voucher->username, password: $voucher->password, profile: $voucher->profile, timeLimit: $voucher->time_limit, dataLimit: $voucher->data_limit, comment: 'Voucher #' . $voucher->id);
            $voucher->update(['mikrotik_synced' => $synced]);
            $msg = $synced ? "Voucher <strong>{$voucher->username}</strong> synced." : "Sync failed for <strong>{$voucher->username}</strong>.";
            return redirect()->back()->with($synced ? 'success' : 'error', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sync error: ' . $e->getMessage());
        }
    }

    // ── EXPORTS ───────────────────────────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        $vouchers = $this->filteredQuery($request)->get();
        $filename = 'vouchers_' . now()->format('Ymd_His') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
        return response()->stream(function () use ($vouchers) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['ID', 'Username', 'Password', 'Profile', 'Status', 'Time Limit', 'Data Limit', 'Price', 'Batch', 'Synced', 'Created']);
            foreach ($vouchers as $v) {
                fputcsv($h, [$v->id, $v->username, $v->password, $v->profile, $v->status, $v->time_limit_formatted, $v->data_limit_formatted, $v->price ?? '', $v->batch ?? '', $v->mikrotik_synced ? 'Yes' : 'No', $v->created_at->format('Y-m-d H:i:s')]);
            }
            fclose($h);
        }, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $vouchers = $this->filteredQuery($request)->get();
        return Pdf::loadView('vouchers.pdf', compact('vouchers'))
            ->setPaper('a4', 'landscape')
            ->download('vouchers_' . now()->format('Ymd_His') . '.pdf');
    }

    public function printVouchers(Request $request)
    {
        $vouchers = $this->filteredQuery($request)->get();
        return view('vouchers.print', compact('vouchers'));
    }


    // ── SYNC ALL FROM MIKROTIK ────────────────────────────────────────────────
    public function syncAll()
    {
        $router = RouterSession::router();
        
        try {
            $mikrotik = VoucherMikroTikService::fromSession();
            
            // Get all hotspot users from MikroTik
            $usersJson = $mikrotik->runCommand('/ip/hotspot/user/print');
            $users = json_decode($usersJson, true);

            if (!is_array($users)) {
                return response()->json(['error' => 'Failed to fetch users from MikroTik'], 500);
            }

            // Get active sessions
            $activeJson = $mikrotik->runCommand('/ip/hotspot/active/print');
            $activeSessions = json_decode($activeJson, true) ?: [];
            $activeByUsername = collect($activeSessions)->keyBy('user');

            $synced = 0;
            foreach ($users as $user) {
                $username = $user['name'] ?? null;
                if (!$username) continue;

                $voucher = Voucher::where('router_id', $router->id)
                    ->where('username', $username)
                    ->first();

                if (!$voucher) continue;

                $this->updateVoucherFromMikroTik($voucher, $user, $activeByUsername->get($username));
                $synced++;
            }

            return response()->json(['success' => true, 'synced' => $synced]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function updateVoucherFromMikroTik(Voucher $voucher, array $user, ?array $activeSession)
    {
        $data = [];

        // Parse uptime and bytes
        $uptime   = $this->parseUptime($user['uptime'] ?? '0s');
        $bytesIn  = (int) ($user['bytes-in'] ?? 0);
        $bytesOut = (int) ($user['bytes-out'] ?? 0);

        // Update usage data if voucher has been used
        if ($uptime > 0 || $bytesIn > 0 || $bytesOut > 0) {
            if (!$voucher->first_used_at) {
                $data['first_used_at'] = now();
            }
            $data['last_used_at']  = now();
            $data['bytes_in']      = $bytesIn;
            $data['bytes_out']     = $bytesOut;
            $data['session_time']  = $uptime;

            if ($activeSession) {
                $data['last_caller_id'] = $activeSession['mac-address'] ?? null;
                $data['last_ip']        = $activeSession['address'] ?? null;
            }
        }

        // Determine status
        $disabled = isset($user['disabled']) && $user['disabled'] === 'true';
        
        if ($disabled) {
            $data['status'] = 'disabled';
        } elseif ($activeSession) {
            $data['status'] = 'used'; // Currently active
        } elseif ($uptime > 0 || $bytesIn > 0) {
            // Has been used but not currently active
            $limitUptime = $this->parseUptime($user['limit-uptime'] ?? '0s');
            if ($limitUptime > 0 && $uptime >= $limitUptime) {
                $data['status'] = 'expired';
            } else {
                $data['status'] = 'used';
            }
        } else {
            $data['status'] = 'active'; // Never used
        }

        if (!empty($data)) {
            $voucher->update($data);
        }
    }

    private function parseUptime(string $uptime): int
    {
        preg_match_all('/(\d+)([dhms])/', $uptime, $matches, PREG_SET_ORDER);
        $seconds = 0;
        foreach ($matches as $match) {
            $val = (int) $match[1];
            $seconds += match($match[2]) {
                'd' => $val * 86400,
                'h' => $val * 3600,
                'm' => $val * 60,
                's' => $val,
                default => 0,
            };
        }
        return $seconds;
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────
    /** Ensure voucher belongs to the currently connected router */
    private function authorizeVoucher(Voucher $voucher): void
    {
        if ($voucher->router_id !== $this->routerId()) {
            abort(403, 'This voucher does not belong to your current router.');
        }
    }

    private function filteredQuery(Request $request)
    {
        $query = Voucher::where('router_id', $this->routerId())->latest();
        if ($s = $request->get('search'))  $query->search($s);
        if ($s = $request->get('status'))  $query->where('status', $s);
        if ($p = $request->get('profile')) $query->where('profile', $p);
        if ($b = $request->get('batch'))   $query->where('batch', $b);
        return $query;
    }

    private function bulkDelete(Voucher $v): void { if ($v->mikrotik_synced) { try { $this->mikrotik()->removeUser($v->username); } catch (\Exception) {} } $v->delete(); }
    private function bulkDisable(Voucher $v): void { $v->update(['status' => 'disabled']); if ($v->mikrotik_synced) { try { $this->mikrotik()->disableUser($v->username); } catch (\Exception) {} } }
    private function bulkEnable(Voucher $v): void { $v->update(['status' => 'active']); if ($v->mikrotik_synced) { try { $this->mikrotik()->enableUser($v->username); } catch (\Exception) {} } }
    private function bulkSync(Voucher $v): void { try { $ok = $this->mikrotik()->pushVoucher(username: $v->username, password: $v->password, profile: $v->profile, timeLimit: $v->time_limit, dataLimit: $v->data_limit); $v->update(['mikrotik_synced' => $ok]); } catch (\Exception) {} }
}
