<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use IvanBaric\Corexis\Tests\Fixtures\Models\CorexisContentModel;
use IvanBaric\Corexis\Tests\Fixtures\Resolvers\FixedTenantResolver;
use IvanBaric\Corexis\Tests\TestCase;

class HasUniqueSlugTest extends TestCase
{
    public function test_it_generates_stable_unique_slugs_within_the_configured_scope(): void
    {
        $this->enableTenant(10);

        $first = CorexisContentModel::query()->create(['title' => 'Školska zadruga']);
        $second = CorexisContentModel::query()->create(['title' => 'Školska zadruga']);

        $first->update(['title' => 'Promijenjeni naslov']);

        $this->assertSame('skolska-zadruga', $first->fresh()->slug);
        $this->assertSame('skolska-zadruga-1', $second->slug);
    }

    public function test_the_same_slug_can_be_used_by_different_tenants(): void
    {
        $this->enableTenant(10);
        $first = CorexisContentModel::query()->create(['title' => 'Isti naslov']);

        FixedTenantResolver::$id = 20;
        $second = CorexisContentModel::query()->create(['title' => 'Isti naslov']);

        $this->assertSame('isti-naslov', $first->slug);
        $this->assertSame('isti-naslov', $second->slug);
    }

    public function test_it_preserves_an_explicit_slug_and_can_regenerate_it_explicitly(): void
    {
        $this->enableTenant(10);

        $first = CorexisContentModel::query()->create([
            'title' => 'Prvi naslov',
            'slug' => 'rucni-slug',
        ]);
        $second = CorexisContentModel::query()->create(['title' => 'Prvi naslov']);

        $second->regenerateSlug()->save();

        $this->assertSame('rucni-slug', $first->slug);
        $this->assertSame('prvi-naslov', $second->slug);
    }

    public function test_it_normalizes_and_deduplicates_explicit_slugs(): void
    {
        $this->enableTenant(10);

        $first = CorexisContentModel::query()->create([
            'title' => 'Prvi naslov',
            'slug' => 'Ručni slug',
        ]);
        $second = CorexisContentModel::query()->create([
            'title' => 'Drugi naslov',
            'slug' => 'Ručni slug',
        ]);

        $this->assertSame('rucni-slug', $first->slug);
        $this->assertSame('rucni-slug-1', $second->slug);
    }

    private function enableTenant(int $tenantId): void
    {
        FixedTenantResolver::$id = $tenantId;
        config()->set('corexis.tenancy.enabled', true);
        config()->set('corexis.tenancy.resolver', FixedTenantResolver::class);
    }
}
