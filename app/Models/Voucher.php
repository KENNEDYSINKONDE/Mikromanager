<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'router_id',
        'username',
        'password',
        'profile',
        'status',
        'time_limit',
        'data_limit',
        'price',
        'note',
        'batch',
        'mikrotik_synced',
        'expires_at',
    ];

    protected $casts = [
        'mikrotik_synced' => 'boolean',
        'expires_at'      => 'datetime',
        'price'           => 'decimal:2',
    ];


    // ── Usage tracking helpers ───────────────────────────────────────────────
    public function getTotalBytesAttribute(): int
    {
        return $this->bytes_in + $this->bytes_out;
    }

    public function getBytesInFormattedAttribute(): string
    {
        return $this->formatBytes($this->bytes_in);
    }

    public function getBytesOutFormattedAttribute(): string
    {
        return $this->formatBytes($this->bytes_out);
    }

    public function getTotalBytesFormattedAttribute(): string
    {
        return $this->formatBytes($this->total_bytes);
    }

    public function getSessionTimeFormattedAttribute(): string
    {
        if (!$this->session_time) return '0s';
        
        $hours   = floor($this->session_time / 3600);
        $minutes = floor(($this->session_time % 3600) / 60);
        $seconds = $this->session_time % 60;

        if ($hours > 0) return "{$hours}h {$minutes}m";
        if ($minutes > 0) return "{$minutes}m {$seconds}s";
        return "{$seconds}s";
    }

    public function isUsed(): bool
    {
        return in_array($this->status, ['used', 'expired']);
    }

    public function hasBeenUsed(): bool
    {
        return $this->first_used_at !== null;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    // tenant() provided by BelongsToTenant trait
    public function router()
    {
        return $this->belongsTo(MikroTikRouter::class, 'router_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeActive($query)    { return $query->where('status', 'active'); }
    public function scopeUsed($query)      { return $query->where('status', 'used'); }
    public function scopeExpired($query)   { return $query->where('status', 'expired'); }

    public function scopeByProfile($query, string $profile) { return $query->where('profile', $profile); }
    public function scopeByBatch($query, string $batch)     { return $query->where('batch', $batch); }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('username', 'like', "%{$term}%")
              ->orWhere('batch',   'like', "%{$term}%")
              ->orWhere('note',    'like', "%{$term}%")
              ->orWhere('profile', 'like', "%{$term}%");
        });
    }

    // ── Accessors ─────────────────────────────────────────────────────────────
    public function getTimeLimitFormattedAttribute(): string
    {
        if (!$this->time_limit) return 'Unlimited';
        $h = floor($this->time_limit / 3600);
        $m = floor(($this->time_limit % 3600) / 60);
        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }

    public function getDataLimitFormattedAttribute(): string
    {
        if (!$this->data_limit) return 'Unlimited';
        $gb = $this->data_limit / (1024 ** 3);
        $mb = $this->data_limit / (1024 ** 2);
        return $gb >= 1 ? round($gb, 2) . ' GB' : round($mb, 2) . ' MB';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'success',
            'used'     => 'secondary',
            'expired'  => 'danger',
            'disabled' => 'warning',
            default    => 'secondary',
        };
    }
}
