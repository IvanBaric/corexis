<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use Illuminate\Support\Str;
use IvanBaric\Corexis\Tests\Fixtures\Models\CorexisContentModel;
use IvanBaric\Corexis\Tests\TestCase;

class HasUuidTest extends TestCase
{
    public function test_it_assigns_a_uuid_v7_and_uses_it_for_route_binding(): void
    {
        config()->set('corexis.tenancy.enabled', false);

        $model = CorexisContentModel::query()->create(['title' => 'Record']);

        $this->assertTrue(Str::isUuid($model->uuid, 7));
        $this->assertSame('uuid', $model->getRouteKeyName());
        $this->assertSame($model->uuid, $model->getRouteKey());
    }

    public function test_it_preserves_an_explicit_uuid(): void
    {
        config()->set('corexis.tenancy.enabled', false);
        $uuid = (string) Str::uuid7();

        $model = CorexisContentModel::query()->create([
            'uuid' => $uuid,
            'title' => 'Imported record',
        ]);

        $this->assertSame($uuid, $model->uuid);
    }
}
