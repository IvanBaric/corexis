<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Corexis\Support\IdempotencyStore;
use IvanBaric\Corexis\Tests\Fixtures\Models\TenantModel;
use IvanBaric\Corexis\Tests\TestCase;

class IdempotencyStoreTest extends TestCase
{
    public function test_it_runs_an_operation_once_for_the_same_key(): void
    {
        $calls = 0;
        $store = app(IdempotencyStore::class);

        $first = $store->run('billing', 'confirm_payment', 'payment-key', function () use (&$calls): ActionResult {
            $calls++;

            return ActionResult::success('Confirmed.', ['subscription_id' => 10], 'confirmed');
        });

        $second = $store->run('billing', 'confirm_payment', 'payment-key', function () use (&$calls): ActionResult {
            $calls++;

            return ActionResult::success('Should not run.');
        });

        $this->assertSame(1, $calls);
        $this->assertTrue($first->success);
        $this->assertTrue($second->success);
        $this->assertSame('Confirmed.', $second->message);
        $this->assertSame('confirmed', $second->code);
        $this->assertSame(['subscription_id' => 10], $second->data);
    }

    public function test_it_replays_expected_error_results(): void
    {
        $calls = 0;
        $store = app(IdempotencyStore::class);

        $first = $store->run('billing', 'confirm_payment', 'failed-key', function () use (&$calls): ActionResult {
            $calls++;

            return ActionResult::error(
                message: 'Provider rejected payment.',
                code: 'provider_failed',
                data: ['provider' => 'stripe'],
                errors: ['payment' => ['Rejected.']],
            );
        });

        $second = $store->run('billing', 'confirm_payment', 'failed-key', function () use (&$calls): ActionResult {
            $calls++;

            return ActionResult::success('Should not run.');
        });

        $this->assertSame(1, $calls);
        $this->assertFalse($first->success);
        $this->assertFalse($second->success);
        $this->assertSame('provider_failed', $second->code);
        $this->assertSame(['provider' => 'stripe'], $second->data);
        $this->assertSame(['payment' => ['Rejected.']], $second->errors);
    }

    public function test_blank_key_bypasses_idempotency(): void
    {
        $calls = 0;
        $store = app(IdempotencyStore::class);

        $store->run('billing', 'confirm_payment', '', function () use (&$calls): ActionResult {
            $calls++;

            return ActionResult::success('First.');
        });

        $store->run('billing', 'confirm_payment', '', function () use (&$calls): ActionResult {
            $calls++;

            return ActionResult::success('Second.');
        });

        $this->assertSame(2, $calls);
    }

    public function test_it_stores_safe_summaries_for_model_data(): void
    {
        $model = TenantModel::query()->create(['name' => 'Demo']);
        $store = app(IdempotencyStore::class);

        $store->run('demo', 'model_result', 'model-key', fn (): ActionResult => ActionResult::success(
            message: 'Stored.',
            data: [
                'model' => $model,
                'url' => 'https://example.test/pay',
            ],
        ));

        $result = $store->run('demo', 'model_result', 'model-key', fn (): ActionResult => ActionResult::success('Should not run.'));

        $this->assertSame([
            'model' => [
                'model' => TenantModel::class,
                'key' => $model->getKey(),
                'uuid' => null,
            ],
            'url' => 'https://example.test/pay',
        ], $result->data);
    }
}
