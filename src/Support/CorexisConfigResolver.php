<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use IvanBaric\Corexis\Contracts\ActorResolver;
use IvanBaric\Corexis\Contracts\LocaleResolver;
use IvanBaric\Corexis\Contracts\SourceResolver;
use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Corexis\Resolvers\AppLocaleResolver;
use IvanBaric\Corexis\Resolvers\AuthActorResolver;
use IvanBaric\Corexis\Resolvers\NullTenantResolver;
use IvanBaric\Corexis\Resolvers\RequestSourceResolver;

final class CorexisConfigResolver
{
    /** @return class-string<TenantResolver> */
    public static function tenantResolver(): string
    {
        return app(ConfigResolver::class)->implementation(
            key: 'corexis.tenancy.resolver',
            default: NullTenantResolver::class,
            expectedType: TenantResolver::class,
        );
    }

    /** @return class-string<LocaleResolver> */
    public static function localeResolver(): string
    {
        return app(ConfigResolver::class)->implementation(
            key: 'corexis.locale.resolver',
            default: AppLocaleResolver::class,
            expectedType: LocaleResolver::class,
        );
    }

    /** @return class-string<ActorResolver> */
    public static function actorResolver(): string
    {
        return app(ConfigResolver::class)->implementation(
            key: 'corexis.actor.resolver',
            default: AuthActorResolver::class,
            expectedType: ActorResolver::class,
        );
    }

    /** @return class-string<SourceResolver> */
    public static function sourceResolver(): string
    {
        return app(ConfigResolver::class)->implementation(
            key: 'corexis.source.resolver',
            default: RequestSourceResolver::class,
            expectedType: SourceResolver::class,
        );
    }

    public static function idempotencyKeysTable(): string
    {
        return app(ConfigResolver::class)->table(
            key: 'corexis.idempotency.table',
            default: 'corexis_idempotency_keys',
        );
    }
}
