<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Concerns;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Data\ActionResult;

trait UsesOptimisticLocking
{
    protected function pullExpectedLockVersion(array &$data, string $key = 'lock_version'): ?int
    {
        if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
            unset($data[$key]);

            return null;
        }

        $version = (int) $data[$key];
        unset($data[$key]);

        return $version;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function saveWithOptimisticLock(Model $model, array $attributes, ?int $expectedLockVersion): bool
    {
        if ($expectedLockVersion === null) {
            $model->fill($attributes)->save();

            return true;
        }

        if (! method_exists($model, 'saveWithLockVersion')) {
            $model->fill($attributes)->save();

            return true;
        }

        return (bool) $model->saveWithLockVersion($attributes, $expectedLockVersion);
    }

    protected function staleModelResult(?string $message = null): ActionResult
    {
        $message ??= __('corexis::corexis.concurrency.stale_model');

        return ActionResult::error(
            message: $message,
            errors: [
                'lock_version' => [$message],
            ],
            code: 'conflict.stale_model',
        );
    }
}
