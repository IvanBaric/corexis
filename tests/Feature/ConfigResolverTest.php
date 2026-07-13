<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Exceptions\InvalidConfiguration;
use IvanBaric\Corexis\Support\ConfigResolver;
use IvanBaric\Corexis\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConfigResolverTest extends TestCase
{
    public function test_uses_default_model_when_config_is_null(): void
    {
        config()->set('demo.models.record', null);

        $this->assertSame(ConfigResolverDefaultModel::class, $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
            expectedType: ConfigResolverDefaultModel::class,
        ));
    }

    public function test_uses_configured_model_when_valid(): void
    {
        config()->set('demo.models.record', ConfigResolverConfiguredModel::class);

        $this->assertSame(ConfigResolverConfiguredModel::class, $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
            expectedType: ConfigResolverDefaultModel::class,
        ));
    }

    public function test_rejects_model_with_wrong_base_type(): void
    {
        config()->set('demo.models.record', ConfigResolverOtherModel::class);

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('demo.models.record');
        $this->expectExceptionMessage(ConfigResolverDefaultModel::class);

        $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
            expectedType: ConfigResolverDefaultModel::class,
        );
    }

    public function test_rejects_missing_class(): void
    {
        config()->set('demo.models.record', 'App\\Missing\\Record');

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('demo.models.record');

        $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
        );
    }

    public function test_rejects_abstract_class(): void
    {
        config()->set('demo.models.record', ConfigResolverAbstractModel::class);

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('demo.models.record');

        $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
        );
    }

    public function test_rejects_scalar_class_value(): void
    {
        config()->set('demo.models.record', 123);

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('demo.models.record');

        $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
        );
    }

    public function test_uses_default_and_configured_table_names(): void
    {
        config()->set('demo.tables.records', null);

        $this->assertSame('demo_records', $this->resolver()->table(
            key: 'demo.tables.records',
            default: 'demo_records',
        ));

        config()->set('demo.tables.records', 'custom_records');

        $this->assertSame('custom_records', $this->resolver()->table(
            key: 'demo.tables.records',
            default: 'demo_records',
        ));
    }

    public function test_accepts_schema_qualified_table_names(): void
    {
        config()->set('demo.tables.records', 'tenant.demo_records');

        $this->assertSame('tenant.demo_records', $this->resolver()->table(
            key: 'demo.tables.records',
            default: 'demo_records',
        ));
    }

    #[DataProvider('invalidTableProvider')]
    public function test_rejects_invalid_table_names(mixed $value): void
    {
        config()->set('demo.tables.records', $value);

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('demo.tables.records');

        $this->resolver()->table(
            key: 'demo.tables.records',
            default: 'demo_records',
        );
    }

    public static function invalidTableProvider(): array
    {
        return [
            'empty' => [''],
            'whitespace' => ['   '],
            'starts with number' => ['1records'],
            'contains dash' => ['demo-records'],
            'contains spaces' => ['demo records'],
            'too many segments' => ['one.two.three'],
            'scalar' => [123],
        ];
    }

    public function test_config_set_changes_are_not_cached(): void
    {
        config()->set('demo.models.record', ConfigResolverDefaultModel::class);

        $this->assertSame(ConfigResolverDefaultModel::class, $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
            expectedType: ConfigResolverDefaultModel::class,
        ));

        config()->set('demo.models.record', ConfigResolverConfiguredModel::class);

        $this->assertSame(ConfigResolverConfiguredModel::class, $this->resolver()->model(
            key: 'demo.models.record',
            default: ConfigResolverDefaultModel::class,
            expectedType: ConfigResolverDefaultModel::class,
        ));
    }

    public function test_invalid_configuration_messages_do_not_include_raw_values(): void
    {
        config()->set('demo.models.record', 'secret-token-like-value');

        try {
            $this->resolver()->model(
                key: 'demo.models.record',
                default: ConfigResolverDefaultModel::class,
            );
        } catch (InvalidConfiguration $exception) {
            $this->assertStringContainsString('demo.models.record', $exception->getMessage());
            $this->assertStringNotContainsString('secret-token-like-value', $exception->getMessage());

            return;
        }

        $this->fail('Expected InvalidConfiguration exception.');
    }

    private function resolver(): ConfigResolver
    {
        return app(ConfigResolver::class);
    }
}

class ConfigResolverDefaultModel extends Model {}

final class ConfigResolverConfiguredModel extends ConfigResolverDefaultModel {}

final class ConfigResolverOtherModel extends Model {}

abstract class ConfigResolverAbstractModel extends Model {}
