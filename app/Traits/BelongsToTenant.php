<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

/**
 * BelongsToTenant
 * ─────────────────────────────────────────────────────────────────────────────
 * Add this trait to ANY model that must be scoped to a tenant.
 * It automatically:
 *   1. Filters all queries to the current tenant (global scope)
 *   2. Sets tenant_id on every new record (creating event)
 *   3. Provides a relationship back to the tenant
 *
 * Usage — add to a model:
 *   use App\Traits\BelongsToTenant;
 *   class Voucher extends Model { use BelongsToTenant; }
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // ── Global scope: all queries auto-filtered by tenant ─────────────────
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenantId = static::currentTenantId();
            if ($tenantId) {
                $query->where((new static)->getTable() . '.tenant_id', $tenantId);
            }
        });

        // ── Auto-set tenant_id on every new record ────────────────────────────
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = static::currentTenantId();
            }
        });
    }

    // ── Relationship ──────────────────────────────────────────────────────────
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Get current tenant ID from the app container ──────────────────────────
    // SetTenantContext middleware binds the tenant to the container on each request
    protected static function currentTenantId(): ?int
    {
        try {
            if (app()->bound('current.tenant')) {
                return app('current.tenant')->id;
            }
        } catch (\Exception) {}
        return null;
    }

    // ── Escape hatch: bypass tenant scope when needed (e.g. admin panel) ──────
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
