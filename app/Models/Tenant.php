<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'email', 'slug', 'phone', 'address', 'logo',
        'plan', 'status',
        'trial_ends_at', 'subscription_ends_at',
        'max_routers', 'max_vouchers', 'max_users',
    ];

    protected $casts = [
        'trial_ends_at'         => 'datetime',
        'subscription_ends_at'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function routers()
    {
        return $this->hasMany(MikroTikRouter::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    // ── Status checks ────────────────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->plan === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isTrialExpired(): bool
    {
        return $this->plan === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->isOnTrial()) return true;
        return $this->subscription_ends_at
            && $this->subscription_ends_at->isFuture();
    }

    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at) return 0;
        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    // ── Plan limit checks ────────────────────────────────────────────────────
    public function canAddRouter(): bool
    {
        return $this->routers()->count() < $this->max_routers;
    }

    public function canAddVoucher(): bool
    {
        return $this->vouchers()->count() < $this->max_vouchers;
    }

    public function canAddUser(): bool
    {
        return $this->users()->count() < $this->max_users;
    }

    public function routersLeft(): int
    {
        return max(0, $this->max_routers - $this->routers()->count());
    }

    public function vouchersLeft(): int
    {
        return max(0, $this->max_vouchers - $this->vouchers()->count());
    }

    // ── Plan helpers ─────────────────────────────────────────────────────────
    public function getPlanBadgeAttribute(): string
    {
        return match ($this->plan) {
            'trial'      => 'warning',
            'starter'    => 'info',
            'pro'        => 'primary',
            'enterprise' => 'success',
            default      => 'secondary',
        };
    }

    public function getPlanLabelAttribute(): string
    {
        return match ($this->plan) {
            'trial'      => '🕐 Trial',
            'starter'    => 'Starter',
            'pro'        => '⭐ Pro',
            'enterprise' => '🏢 Enterprise',
            default      => ucfirst($this->plan),
        };
    }

    // ── Static: plan definitions ─────────────────────────────────────────────
    public static function planDefaults(string $plan): array
    {
        return match ($plan) {
            'trial'      => ['max_routers' => 1, 'max_vouchers' => 100,  'max_users' => 1],
            'starter'    => ['max_routers' => 1, 'max_vouchers' => 500,  'max_users' => 2],
            'pro'        => ['max_routers' => 5, 'max_vouchers' => 5000, 'max_users' => 10],
            'enterprise' => ['max_routers' => 99,'max_vouchers' => 99999,'max_users' => 99],
            default      => ['max_routers' => 1, 'max_vouchers' => 100,  'max_users' => 1],
        };
    }

    // ── Generate slug from name ───────────────────────────────────────────────
    public static function generateSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)));
        $base = $slug;
        $i    = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
