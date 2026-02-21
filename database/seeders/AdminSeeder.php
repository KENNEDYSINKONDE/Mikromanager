<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo ISP tenant ───────────────────────────────────────────────────
        $tenant = Tenant::firstOrCreate(
            ['email' => 'admin@mikrotik.local'],
            [
                'name'                 => 'Demo ISP',
                'slug'                 => 'demo-isp',
                'plan'                 => 'enterprise',
                'status'               => 'active',
                'trial_ends_at'        => null,
                'subscription_ends_at' => now()->addYears(10),
                ...Tenant::planDefaults('enterprise'),
            ]
        );

        // ── ISP owner user ────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@mikrotik.local'],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Demo Admin',
                'password'  => Hash::make('admin123'),
                'role'      => 'owner',
                'is_active' => true,
            ]
        );

        // ── Superadmin (no tenant — manages everything) ───────────────────────
        User::firstOrCreate(
            ['email' => 'superadmin@mikrotik.local'],
            [
                'tenant_id' => null,
                'name'      => 'Super Admin',
                'password'  => Hash::make('super123'),
                'role'      => 'superadmin',
                'is_active' => true,
            ]
        );

        $this->command->info('');
        $this->command->info('  ✅ Accounts created:');
        $this->command->info('');
        $this->command->info('  ISP Owner:');
        $this->command->info('     Email   : admin@mikrotik.local');
        $this->command->info('     Password: admin123');
        $this->command->info('');
        $this->command->info('  Super Admin (admin panel):');
        $this->command->info('     Email   : superadmin@mikrotik.local');
        $this->command->info('     Password: super123');
        $this->command->warn('     ⚠  Change passwords after first login!');
        $this->command->info('');
    }
}
