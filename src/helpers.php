<?php

declare(strict_types=1);

use IvanBaric\Corexis\Support\CurrentActor;
use IvanBaric\Corexis\Support\CurrentLocale;
use IvanBaric\Corexis\Support\CurrentSource;
use IvanBaric\Corexis\Support\CurrentTenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use IvanBaric\Corexis\Data\ActionResult;

if (! function_exists('corexis_tenant')) {
    function corexis_tenant(): CurrentTenant
    {
        return app(CurrentTenant::class);
    }
}

if (! function_exists('corexis_tenant_id')) {
    function corexis_tenant_id(): int|string|null
    {
        return corexis_tenant()->id();
    }
}

if (! function_exists('corexis_locale')) {
    function corexis_locale(): CurrentLocale
    {
        return app(CurrentLocale::class);
    }
}

if (! function_exists('corexis_locale_code')) {
    function corexis_locale_code(): ?string
    {
        return corexis_locale()->current();
    }
}

if (! function_exists('corexis_actor')) {
    function corexis_actor(): CurrentActor
    {
        return app(CurrentActor::class);
    }
}

if (! function_exists('corexis_actor_id')) {
    function corexis_actor_id(): int|string|null
    {
        return corexis_actor()->id();
    }
}

if (! function_exists('corexis_source')) {
    function corexis_source(): string
    {
        return app(CurrentSource::class)->current();
    }
}

if (! function_exists('corexis_can')) {
    function corexis_can(string $ability, mixed $arguments = []): bool
    {
        if (corexis_authorization_missing($ability)) {
            return true;
        }

        return Gate::allows($ability, $arguments);
    }
}

if (! function_exists('corexis_authorize')) {
    function corexis_authorize(string $ability, mixed $arguments = []): void
    {
        if (corexis_authorization_missing($ability)) {
            return;
        }

        Gate::authorize($ability, $arguments);
    }
}

if (! function_exists('corexis_authorization_result')) {
    function corexis_authorization_result(string $ability, mixed $arguments = []): ?ActionResult
    {
        if (corexis_authorization_missing($ability)) {
            return null;
        }

        $response = Gate::inspect($ability, $arguments);

        if ($response->allowed()) {
            return null;
        }

        $message = $response->message() ?: __('corexis::corexis.authorization.denied');

        return ActionResult::error(
            message: $message,
            code: 'authorization.denied',
            errors: [
                'authorization' => [$message],
            ],
        );
    }
}

if (! function_exists('corexis_authorization_missing')) {
    function corexis_authorization_missing(string $ability): bool
    {
        if ((bool) config('corexis.authorization.fail_when_missing', false)) {
            return false;
        }

        if (Gate::has($ability)) {
            return false;
        }

        $user = auth()->user();

        if (! $user || ! method_exists($user, 'hasPermission') || ! str_contains($ability, '.')) {
            return true;
        }

        return ! corexis_authorization_ability_is_registered($ability);
    }
}

if (! function_exists('corexis_authorization_ability_is_registered')) {
    function corexis_authorization_ability_is_registered(string $ability): bool
    {
        try {
            if (! Schema::hasTable('permission_items')) {
                return false;
            }

            return DB::table('permission_items')->where('code', $ability)->exists();
        } catch (Throwable) {
            return false;
        }
    }
}
