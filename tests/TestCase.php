<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use IvanBaric\Corexis\CorexisServiceProvider;
use IvanBaric\Corexis\Tests\Fixtures\Models\TenantModel;
use IvanBaric\Corexis\Tests\Fixtures\Models\User;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CorexisServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('auth.defaults.guard', 'web');
        $app['config']->set('auth.defaults.passwords', 'users');
        $app['config']->set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('queue.default', 'sync');
    }

    protected function setUp(): void
    {
        parent::setUp();

        User::clearBootedModels();
        TenantModel::clearBootedModels();

        $this->createSchema();
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('tenant_models');
        Schema::dropIfExists('corexis_idempotency_keys');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_models', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('corexis_idempotency_keys', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('scope')->index();
            $table->string('operation')->index();
            $table->string('idempotency_key');
            $table->string('status')->index();
            $table->string('response_message')->nullable();
            $table->string('response_code')->nullable();
            $table->json('response_data')->nullable();
            $table->json('response_errors')->nullable();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['scope', 'operation', 'idempotency_key'], 'corexis_idempotency_scope_operation_key_unique');
        });
    }
}
