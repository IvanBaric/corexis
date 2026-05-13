<?php

declare(strict_types=1);

use IvanBaric\Corexis\Support\CurrentActor;
use IvanBaric\Corexis\Support\CurrentLocale;
use IvanBaric\Corexis\Support\CurrentSource;
use IvanBaric\Corexis\Support\CurrentTenant;

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
