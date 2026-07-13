<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Corexis\Exceptions\TenantNotResolvedException;

final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $resolver = app(TenantResolver::class);

        if (! $resolver->enabled()) {
            return;
        }

        $tenantId = $resolver->id();

        if ($tenantId === null) {
            throw TenantNotResolvedException::make();
        }

        $builder->where(
            $model->qualifyColumn($model->getTenantColumn()),
            $tenantId,
        );
    }
}
