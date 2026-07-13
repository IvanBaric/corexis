<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use IvanBaric\Corexis\Exceptions\TenantMutationException;
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

        $this->assertNull($model->team_id);
    }

    public function test_it_sets_tenant_id_when_tenancy_is_enabled(): void
    {
        FixedTenantResolver::$id = 456;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $model = TenantModel::query()->create(['name' => 'Record']);

        $this->assertSame(456, $model->team_id);
    }

    public function test_it_overwrites_untrusted_tenant_input_with_the_resolved_tenant(): void
    {
        FixedTenantResolver::$id = 456;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $model = TenantModel::query()->create([
            'name' => 'Record',
            'team_id' => 999,
        ]);

        $this->assertSame(456, $model->team_id);
    }

    public function test_it_supports_custom_tenant_id_column(): void
    {
        FixedTenantResolver::$id = 456;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);
        config()->set('corexis.tenancy.id_column', 'tenant_id');

        $model = TenantModel::query()->create(['name' => 'Record']);

        $this->assertSame(456, $model->tenant_id);
    }

    public function test_it_throws_when_tenant_is_required_but_unresolved(): void
    {
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', UnresolvedTenantResolver::class);
        $this->expectException(TenantNotResolvedException::class);

        TenantModel::query()->create(['name' => 'Record']);
    }

    public function test_it_applies_the_current_tenant_as_a_global_scope(): void
    {
        FixedTenantResolver::$id = 456;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        TenantModel::query()->create(['name' => 'Visible']);
        FixedTenantResolver::$id = 789;
        TenantModel::query()->create(['name' => 'Hidden']);

        $this->assertSame(['Hidden'], TenantModel::query()->pluck('name')->all());
        $this->assertSame(['Visible'], TenantModel::query()->forTenant(456)->pluck('name')->all());
    }

    public function test_it_prevents_regular_tenant_mutation(): void
    {
        FixedTenantResolver::$id = 456;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);

        $model = TenantModel::query()->create(['name' => 'Record']);
        $model->team_id = 789;

        $this->expectException(TenantMutationException::class);

        $model->save();
    }
}
