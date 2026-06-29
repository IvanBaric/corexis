<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use IvanBaric\Corexis\Contracts\ActorResolver;
use IvanBaric\Corexis\Contracts\LocaleResolver;
use IvanBaric\Corexis\Contracts\SourceResolver;
use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Corexis\Resolvers\StaticSourceResolver;
use IvanBaric\Corexis\Support\IdempotencyStore;
use IvanBaric\Corexis\Tests\Fixtures\Resolvers\FixedTenantResolver;
use IvanBaric\Corexis\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_corexis_tenant_id_returns_id_from_resolver(): void
    {
        FixedTenantResolver::$id = 321;
        $this->app->instance(TenantResolver::class, new FixedTenantResolver);

        $this->assertSame(321, corexis_tenant_id());
    }

    public function test_corexis_locale_code_returns_locale(): void
    {
        $this->app->instance(LocaleResolver::class, new class implements LocaleResolver
        {
            public function enabled(): bool
            {
                return true;
            }

            public function current(): ?string
            {
                return 'hr';
            }

            public function default(): ?string
            {
                return 'en';
            }

            public function fallback(): ?string
            {
                return 'en';
            }

            public function available(): array
            {
                return ['hr', 'en'];
            }
        });

        $this->assertSame('hr', corexis_locale_code());
    }

    public function test_corexis_actor_id_returns_actor_id(): void
    {
        $this->app->instance(ActorResolver::class, new class implements ActorResolver
        {
            public function enabled(): bool
            {
                return true;
            }

            public function current(): mixed
            {
                return null;
            }

            public function id(): int|string|null
            {
                return 55;
            }

            public function type(): ?string
            {
                return 'user';
            }
        });

        $this->assertSame(55, corexis_actor_id());
    }

    public function test_corexis_source_returns_current_source(): void
    {
        StaticSourceResolver::use('admin');
        $this->app->instance(SourceResolver::class, new StaticSourceResolver);

        $this->assertSame('admin', corexis_source());

        StaticSourceResolver::reset();
    }

    public function test_corexis_idempotency_returns_store(): void
    {
        $this->assertInstanceOf(IdempotencyStore::class, corexis_idempotency());
    }
}
