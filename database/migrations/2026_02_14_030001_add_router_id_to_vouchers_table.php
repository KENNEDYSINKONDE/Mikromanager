<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Add router_id after id — this scopes every voucher to a specific router
            $table->foreignId('router_id')
                  ->after('id')
                  ->nullable()                    // nullable for existing rows
                  ->constrained('mikrotik_routers')
                  ->cascadeOnDelete();

            $table->index('router_id');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropColumn('router_id');
        });
    }
};
