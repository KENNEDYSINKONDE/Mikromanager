<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // ISP business name
            $table->string('email')->unique();               // primary contact email
            $table->string('slug')->unique();                // url-friendly identifier
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();

            // Subscription
            $table->enum('plan', ['trial', 'starter', 'pro', 'enterprise'])->default('trial');
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            // Plan limits (cached here for fast enforcement)
            $table->unsignedInteger('max_routers')->default(1);
            $table->unsignedInteger('max_vouchers')->default(500);
            $table->unsignedInteger('max_users')->default(2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
