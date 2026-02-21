<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_routers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();            // each router belongs to a user

            $table->string('name');               // friendly name e.g. "JumuiyaConnect Main"
            $table->string('host');               // IP address e.g. 192.168.0.13
            $table->unsignedSmallInteger('port')->default(8728);
            $table->string('username');           // MikroTik API username
            $table->text('password');             // encrypted with Crypt::encryptString
            $table->string('identity')->nullable(); // router identity fetched on connect
            $table->string('model')->nullable();    // e.g. RB750r2
            $table->string('version')->nullable();  // RouterOS version
            $table->enum('status', ['online', 'offline', 'error'])->default('offline');
            $table->string('last_error')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();

            // one user cannot add the same host:port twice
            $table->unique(['user_id', 'host', 'port']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_routers');
    }
};
