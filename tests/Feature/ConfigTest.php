<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Corexis\Resolvers\NullTenantResolver;
use IvanBaric\Corexis\Tests\Fixtures\Resolvers\FixedTenantResolver;
use IvanBaric\Corexis\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertSame('system', config('corexis.source.default'));
        $this->assertSame('team_id', config('corexis.tenancy.id_column'));
    }

    public function test_config_can_be_published(): void
    {
        if (is_file(config_path('corexis.php'))) {
            unlink(config_path('corexis.php'));
        }

        $this->artisan('vendor:publish', [
            '--tag' => 'corexis-config',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileExists(config_path('corexis.php'));
    }

    public function test_tenancy_disabled_uses_null_tenant_resolver(): void
    {
        config()->set('corexis.tenancy.enabled', false);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $this->assertInstanceOf(NullTenantResolver::class, app(TenantResolver::class));
    }

    public function test_tenancy_enabled_uses_configured_resolver(): void
    {
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $this->assertInstanceOf(FixedTenantResolver::class, app(TenantResolver::class));
    }
}
