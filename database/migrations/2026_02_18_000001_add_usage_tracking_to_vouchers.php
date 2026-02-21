<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Usage tracking from MikroTik
            $table->timestamp('first_used_at')->nullable()->after('mikrotik_synced');
            $table->timestamp('last_used_at')->nullable()->after('first_used_at');
            $table->unsignedBigInteger('bytes_in')->default(0)->after('last_used_at');  // download
            $table->unsignedBigInteger('bytes_out')->default(0)->after('bytes_in');     // upload
            $table->unsignedInteger('session_time')->default(0)->after('bytes_out');    // seconds used
            $table->string('last_caller_id')->nullable()->after('session_time');        // MAC address
            $table->ipAddress('last_ip')->nullable()->after('last_caller_id');
            
            $table->index('first_used_at');
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'first_used_at',
                'last_used_at', 
                'bytes_in',
                'bytes_out',
                'session_time',
                'last_caller_id',
                'last_ip',
            ]);
        });
    }
};
