<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── users ─────────────────────────────────────────────────────────────
        // Use hasColumn to avoid "duplicate column" if already added
        if (!Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                      ->after('id')
                      ->nullable()
                      ->constrained('tenants')
                      ->cascadeOnDelete();
            });
        }

        // Fix the role ENUM to include 'owner' if not already there
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','owner','admin','operator','viewer') NOT NULL DEFAULT 'operator'");

        // ── mikrotik_routers ──────────────────────────────────────────────────
        // Drop FK before dropping the unique index (MySQL requirement)
        if (Schema::hasColumn('mikrotik_routers', 'user_id')) {
            try {
                Schema::table('mikrotik_routers', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropUnique(['user_id', 'host', 'port']);
                });
            } catch (\Exception $e) {
                // Already dropped — continue
            }
        }

        if (!Schema::hasColumn('mikrotik_routers', 'tenant_id')) {
            Schema::table('mikrotik_routers', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                      ->after('id')
                      ->nullable()
                      ->constrained('tenants')
                      ->cascadeOnDelete();

                $table->unique(['tenant_id', 'host', 'port'], 'routers_tenant_host_port_unique');
            });
        }

        // ── vouchers ──────────────────────────────────────────────────────────
        if (!Schema::hasColumn('vouchers', 'tenant_id')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                      ->after('id')
                      ->nullable()
                      ->constrained('tenants')
                      ->cascadeOnDelete();

                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vouchers', 'tenant_id')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('mikrotik_routers', 'tenant_id')) {
            Schema::table('mikrotik_routers', function (Blueprint $table) {
                $table->dropUnique('routers_tenant_host_port_unique');
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
