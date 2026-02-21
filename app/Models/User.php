<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',        // owner | admin | operator | viewer
        'is_active',
        'avatar',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'last_login_at' => 'datetime',
        'is_active'     => 'boolean',
    ];

    // ── Role helpers ──────────────────────────────────────────────────────────
    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isOwner(): bool      { return $this->role === 'owner'; }
    public function isAdmin(): bool      { return in_array($this->role, ['superadmin', 'owner', 'admin']); }
    public function isOperator(): bool   { return $this->role === 'operator'; }
    public function isViewer(): bool     { return $this->role === 'viewer'; }

    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'danger',
            'owner'      => 'dark',
            'admin'      => 'primary',
            'operator'   => 'secondary',
            'viewer'     => 'light',
            default      => 'secondary',
        };
    }

    // ── Login tracking ────────────────────────────────────────────────────────
    public function recordLogin(string $ip): void
    {
        $this->update(['last_login_at' => now(), 'last_login_ip' => $ip]);
    }

    // ── Avatar initials ───────────────────────────────────────────────────────
    public function getInitialsAttribute(): string
    {
        $words    = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper($word[0] ?? '');
        }
        return $initials ?: 'U';
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    // tenant() provided by BelongsToTenant trait
}
