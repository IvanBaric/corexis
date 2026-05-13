<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use IvanBaric\Corexis\Exceptions\TenantNotResolvedException;
use IvanBaric\Corexis\Tests\Fixtures\Models\TenantModel;
use IvanBaric\Corexis\Tests\Fixtures\Resolvers\FixedTenantResolver;
use IvanBaric\Corexis\Tests\Fixtures\Resolvers\UnresolvedTenantResolver;
use IvanBaric\Corexis\Tests\TestCase;

class BelongsToTenantTest extends TestCase
{
    public function test_it_does_nothing_when_tenancy_is_disabled(): void
    {
        config()->set('corexis.tenancy.enabled', false);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $model = TenantModel::query()->create(['name' => 'Record']);

        $this->assertNull($model->tenant_id);
    }

    public function test_it_sets_tenant_id_when_tenancy_is_enabled(): void
    {
        FixedTenantResolver::$id = 456;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $model = TenantModel::query()->create(['name' => 'Record']);

        $this->assertSame(456, $model->tenant_id);
    }

    public function test_it_does_not_overwrite_existing_tenant_id(): void
    {
        FixedTenantResolver::$id = 456;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $model = TenantModel::query()->create([
            'name' => 'Record',
            'tenant_id' => 999,
        ]);

        $this->assertSame(999, $model->tenant_id);
    }

    public function test_it_throws_when_tenant_is_required_but_unresolved(): void
    {
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', UnresolvedTenantResolver::class);
        config()->set('corexis.tenancy.fail_when_unresolved', true);

        $this->expectException(TenantNotResolvedException::class);

        TenantModel::query()->create(['name' => 'Record']);
    }
}
