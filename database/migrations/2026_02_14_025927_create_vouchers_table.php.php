<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('profile')->default('default');
            $table->enum('status', ['active', 'used', 'expired', 'disabled'])->default('active');
            $table->unsignedBigInteger('time_limit')->nullable(); // seconds
            $table->unsignedBigInteger('data_limit')->nullable(); // bytes
            $table->decimal('price', 10, 2)->nullable();
            $table->string('batch')->nullable()->index();
            $table->text('note')->nullable();
            $table->boolean('mikrotik_synced')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('profile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
