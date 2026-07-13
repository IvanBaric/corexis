<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Corexis\Database\Scopes\TenantScope;
use IvanBaric\Corexis\Exceptions\TenantMutationException;
use IvanBaric\Corexis\Exceptions\TenantNotResolvedException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $resolver = app(TenantResolver::class);

            if (! $resolver->enabled()) {
                return;
            }

            $tenantId = $resolver->id();

            if ($tenantId === null) {
                throw TenantNotResolvedException::make();
            }

            $model->setAttribute($model->getTenantColumn(), $tenantId);
        });

        static::updating(function (Model $model): void {
            $column = $model->getTenantColumn();

            if ($model->isDirty($column)) {
                throw TenantMutationException::make($column);
            }
        });
    }

    public function getTenantColumn(): string
    {
        return (string) config('corexis.tenancy.id_column', 'team_id');
    }

    public function scopeForTenant(Builder $query, int|string $tenantId): Builder
    {
        return $query
            ->withoutGlobalScope(TenantScope::class)
            ->where($this->getTenantColumn(), $tenantId);
    }
}
