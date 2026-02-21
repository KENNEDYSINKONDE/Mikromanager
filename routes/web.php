<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RouterAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MikroTikController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Mikrotik\VoucherController;
use App\Http\Controllers\Mikrotik\CommandController;
use App\Http\Controllers\Mikrotik\RouterboardController;
use App\Http\Controllers\Mikrotik\BridgeController;

// ============================================================
// PUBLIC — guests only (redirects if already logged in)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/',          [AuthController::class,   'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class,   'login'])->name('login.post');
    Route::get('/register',  [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.post');
});

// ============================================================
// AUTH REQUIRED — logged in, no router session needed yet
// ============================================================
Route::middleware(['auth', 'tenant.active'])->group(function () {

    Route::post('/logout',           [AuthController::class,      'logout'])->name('logout');
    Route::get('/router-select',     [AuthController::class,      'showRouterSelect'])->name('router.select');
    Route::post('/router-connect',   [RouterAuthController::class, 'connect'])->name('router.connect');
    Route::post('/router-disconnect',[RouterAuthController::class, 'disconnect'])->name('router.disconnect');

    // User profile settings
    Route::get('/profile',           [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile',           [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password',  [AuthController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/avatar',    [AuthController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [AuthController::class, 'removeAvatar'])->name('profile.avatar.remove');
});

// ============================================================
// AUTH + ROUTER SESSION REQUIRED — full dashboard access
// ============================================================
Route::middleware(['auth', 'tenant.active', 'router.session'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('layout.dashboard');

    // ── System ───────────────────────────────────────────────────────────────
    Route::get('/status',               [MikroTikController::class,    'status'])->name('mikrotik.status');
    Route::get('/mikrotik/routerboard', [RouterboardController::class, 'index'])->name('mikrotik.routerboard');
    Route::get('/mikrotik/interfaces',  [MikroTikController::class,    'interfaces'])->name('mikrotik.interfaces');

    // ── Bridges ──────────────────────────────────────────────────────────────
    Route::prefix('mikrotik')->group(function () {
        Route::get('bridges',      [BridgeController::class, 'bridges'])->name('mikrotik.bridges');
        Route::get('bridge-ports', [BridgeController::class, 'ports'])->name('mikrotik.bridge.ports');
        Route::get('bridge-hosts', [BridgeController::class, 'hosts'])->name('mikrotik.bridge.hosts');
    });

    // ── Hotspot ───────────────────────────────────────────────────────────────
    Route::get('/mikrotik/servers',          [MikroTikController::class, 'hotspotServers'])->name('hotspot.servers');
    Route::get('/mikrotik/servers/profiles', [MikroTikController::class, 'ServerProfiles'])->name('hotspot.serverProfile');

    // ── User Profiles (against MikroTik) ─────────────────────────────────────
    Route::resource('profiles', ProfileController::class);

    // ── Vouchers — specific routes BEFORE resource() ──────────────────────────
    Route::post('vouchers/generate',       [VoucherController::class, 'generate'])->name('vouchers.generate');
    Route::post('vouchers/bulk',           [VoucherController::class, 'bulkAction'])->name('vouchers.bulk');
    Route::get('vouchers/export/csv',      [VoucherController::class, 'exportCsv'])->name('vouchers.export.csv');
    Route::get('vouchers/export/pdf',      [VoucherController::class, 'exportPdf'])->name('vouchers.export.pdf');
    Route::get('vouchers/print',           [VoucherController::class, 'printVouchers'])->name('vouchers.print');
    Route::post('vouchers/{voucher}/sync', [VoucherController::class, 'sync'])->name('vouchers.sync');
    Route::post('vouchers/sync-all',       [VoucherController::class, 'syncAll'])->name('vouchers.sync.all');
    Route::resource('vouchers', VoucherController::class);

    // ── Terminal ──────────────────────────────────────────────────────────────
    Route::get('/mikrotik/terminal',  fn() => view('mikrotik.terminal'))->name('mikrotik.terminal');
    Route::post('/mikrotik/command',  [CommandController::class, 'execute'])->name('mikrotik.command');

});

// ── TEMPORARY DEBUG — remove after fixing ────────────────────────────────────
Route::get('/debug-router', function () {
    $router = \App\Services\RouterSession::router();
    if (!$router) return response()->json(['error' => 'No router in session']);
    return response()->json([
        'session_id'  => \App\Services\RouterSession::id(),
        'router_id'   => $router->id,
        'host'        => $router->host,
        'port'        => $router->port,
        'username'    => $router->username,
        'password_set'=> !empty($router->getRawOriginal('password')),
        'status'      => $router->status,
        'tenant_id'   => $router->tenant_id,
        'user_id'     => $router->user_id,
    ]);
})->middleware('auth');


    // ── Reports ────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('vouchers',     [\App\Http\Controllers\Reports\VoucherReportController::class, 'index'])->name('vouchers');
        Route::get('vouchers/pdf', [\App\Http\Controllers\Reports\VoucherReportController::class, 'exportPdf'])->name('vouchers.pdf');
        Route::get('vouchers/csv', [\App\Http\Controllers\Reports\VoucherReportController::class, 'exportCsv'])->name('vouchers.csv');
    });

// ============================================================
// ADMIN PANEL — superadmin only
// ============================================================
Route::middleware(['auth', 'superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [\App\Http\Controllers\Admin\TenantController::class, 'dashboard'])->name('dashboard');

        // Tenant CRUD
        Route::get('tenants',                  [\App\Http\Controllers\Admin\TenantController::class, 'index'])->name('tenants.index');
        Route::get('tenants/create',           [\App\Http\Controllers\Admin\TenantController::class, 'create'])->name('tenants.create');
        Route::post('tenants',                 [\App\Http\Controllers\Admin\TenantController::class, 'store'])->name('tenants.store');
        Route::get('tenants/{tenant}',         [\App\Http\Controllers\Admin\TenantController::class, 'show'])->name('tenants.show');
        Route::get('tenants/{tenant}/edit',    [\App\Http\Controllers\Admin\TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('tenants/{tenant}',         [\App\Http\Controllers\Admin\TenantController::class, 'update'])->name('tenants.update');
        Route::delete('tenants/{tenant}',      [\App\Http\Controllers\Admin\TenantController::class, 'destroy'])->name('tenants.destroy');

        // Tenant actions
        Route::post('tenants/{tenant}/suspend',      [\App\Http\Controllers\Admin\TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate',     [\App\Http\Controllers\Admin\TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('tenants/{tenant}/change-plan',  [\App\Http\Controllers\Admin\TenantController::class, 'changePlan'])->name('tenants.changePlan');
        Route::post('tenants/{tenant}/extend-trial', [\App\Http\Controllers\Admin\TenantController::class, 'extendTrial'])->name('tenants.extendTrial');
        Route::post('tenants/{tenant}/impersonate',  [\App\Http\Controllers\Admin\TenantController::class, 'impersonate'])->name('tenants.impersonate');

        // Stop impersonating
        Route::get('impersonate/stop', [\App\Http\Controllers\Admin\TenantController::class, 'stopImpersonate'])->name('impersonate.stop');
    });
