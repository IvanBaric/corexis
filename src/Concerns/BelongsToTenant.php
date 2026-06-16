<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Concerns;

use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Corexis\Exceptions\TenantNotResolvedException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model): void {
            $resolver = app(TenantResolver::class);

            if (! $resolver->enabled()) {
                return;
            }

            $column = (string) config('corexis.tenancy.id_column', 'team_id');

            if ($model->getAttribute($column) !== null) {
                return;
            }

            $tenantId = $resolver->id();

            if ($tenantId === null) {
                if ((bool) config('corexis.tenancy.fail_when_unresolved', false)) {
                    throw TenantNotResolvedException::make();
                }

                return;
            }

            $model->setAttribute($column, $tenantId);
        });
    }

    public function scopeForCurrentTenant($query): mixed
    {
        $resolver = app(TenantResolver::class);

        if (! $resolver->enabled()) {
            return $query;
        }

        $tenantId = $resolver->id();

        if ($tenantId === null) {
            if ((bool) config('corexis.tenancy.fail_when_unresolved', false)) {
                throw TenantNotResolvedException::make();
            }

            return $query;
        }

        return $query->where((string) config('corexis.tenancy.id_column', 'team_id'), $tenantId);
    }
}
