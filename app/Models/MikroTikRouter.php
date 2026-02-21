<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MikroTikRouter extends Model
{
    use BelongsToTenant;

    protected $table = 'mikrotik_routers';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'host',
        'port',
        'username',
        'password',
        'identity',
        'model',
        'version',
        'status',
        'last_error',
        'note',
        'last_connected_at',
    ];

    protected $casts = [
        'last_connected_at' => 'datetime',
        'port'              => 'integer',
    ];

    // ── Password: always encrypted at rest ───────────────────────────────────
    public function setPasswordAttribute(string $value): void
    {
        try {
            Crypt::decryptString($value);
            $this->attributes['password'] = $value; // already encrypted
        } catch (\Exception) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    public function getPasswordAttribute(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $value;
        }
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    // tenant() provided by BelongsToTenant trait
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'router_id');
    }

    // ── Status helpers ────────────────────────────────────────────────────────
    public function isOnline(): bool { return $this->status === 'online'; }

    public function markOnline(?string $identity = null): void
    {
        $this->update([
            'status'            => 'online',
            'last_connected_at' => now(),
            'last_error'        => null,
            'identity'          => $identity ?? $this->identity,
        ]);
    }

    public function markOffline(?string $error = null): void
    {
        $this->update(['status' => 'offline', 'last_error' => $error]);
    }

    // ── Connection config (password accessor decrypts automatically) ──────────
    public function getConnectionConfig(): array
    {
        if (empty($this->host) || empty($this->username)) {
            throw new \RuntimeException(
                'Router record is incomplete — host or username is missing. Please reconnect.'
            );
        }

        return [
            'host'    => $this->host,
            'user'    => $this->username,
            'pass'    => $this->password,
            'port'    => (int) ($this->port ?: 8728),
            'timeout' => 10,
        ];
    }
}
