<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

final readonly class Corexis
{
    public function tenant(): CurrentTenant
    {
        return app(CurrentTenant::class);
    }

    public function locale(): CurrentLocale
    {
        return app(CurrentLocale::class);
    }

    public function actor(): CurrentActor
    {
        return app(CurrentActor::class);
    }

    public function source(): CurrentSource
    {
        return app(CurrentSource::class);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return config('corexis.'.$key, $default);
    }
}
