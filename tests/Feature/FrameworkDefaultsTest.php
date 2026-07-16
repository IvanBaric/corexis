<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use IvanBaric\Corexis\Support\FrameworkDefaults;
use IvanBaric\Corexis\Tests\TestCase;
use ReflectionProperty;

class FrameworkDefaultsTest extends TestCase
{
    public function test_development_and_test_defaults_are_configured(): void
    {
        $this->assertInstanceOf(CarbonImmutable::class, Date::now());
        $this->assertTrue(Model::preventsLazyLoading());
        $this->assertTrue(Model::preventsSilentlyDiscardingAttributes());
        $this->assertTrue(Model::preventsAccessingMissingAttributes());
        $this->assertTrue(Http::preventingStrayRequests());
        $this->assertFalse($this->destructiveCommandsAreProhibited());
        $this->assertNotEmpty($this->queryDurationHandlers(DB::connection()));
    }

    public function test_required_validation_rules_use_the_shared_message(): void
    {
        app()->setLocale('hr');

        $validator = Validator::make(
            ['name' => null],
            ['name' => ['required']],
        );

        $this->assertSame('Obavezno polje', $validator->errors()->first('name'));
    }

    public function test_production_defaults_are_configured(): void
    {
        $previousEnvironment = app()->environment();

        try {
            app()->detectEnvironment(static fn (): string => 'production');
            app(FrameworkDefaults::class)->configure(app());

            $rules = Password::defaults()->toPasswordRulesString();

            $this->assertFalse(Model::preventsLazyLoading());
            $this->assertTrue($this->destructiveCommandsAreProhibited());
            $this->assertStringContainsString('minlength: 8', $rules);
            $this->assertStringContainsString('required: lower', $rules);
            $this->assertStringContainsString('required: upper', $rules);
            $this->assertStringContainsString('required: digit', $rules);
            $this->assertStringContainsString('required: special', $rules);
        } finally {
            app()->detectEnvironment(static fn (): string => $previousEnvironment);
            app(FrameworkDefaults::class)->configure(app());
        }
    }

    private function destructiveCommandsAreProhibited(): bool
    {
        $property = new ReflectionProperty(FreshCommand::class, 'prohibitedFromRunning');

        return (bool) $property->getValue();
    }

    /**
     * @return array<int, array{has_run: bool, handler: callable}>
     */
    private function queryDurationHandlers(Connection $connection): array
    {
        $property = new ReflectionProperty(Connection::class, 'queryDurationHandlers');

        return $property->getValue($connection);
    }
}
