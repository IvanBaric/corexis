<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Corexis\Models\IdempotencyKey;
use JsonException;
use JsonSerializable;

final class IdempotencyStore
{
    /**
     * @param  Closure(): ActionResult  $callback
     */
    public function run(
        string $scope,
        string $operation,
        ?string $idempotencyKey,
        Closure $callback,
        DateTimeInterface|string|null $expiresAt = null,
    ): ActionResult {
        $idempotencyKey = trim((string) $idempotencyKey);

        if ($idempotencyKey === '' || ! $this->enabled() || ! Schema::hasTable($this->table())) {
            return $callback();
        }

        return DB::transaction(function () use ($scope, $operation, $idempotencyKey, $callback, $expiresAt): ActionResult {
            $record = IdempotencyKey::query()
                ->where('scope', $scope)
                ->where('operation', $operation)
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($record instanceof IdempotencyKey && $record->isCompleted() && ! $record->isExpired()) {
                return $this->toActionResult($record);
            }

            if ($record instanceof IdempotencyKey && $record->isExpired()) {
                $record->delete();
                $record = null;
            }

            if (! $record instanceof IdempotencyKey) {
                $record = IdempotencyKey::query()->create([
                    'scope' => $scope,
                    'operation' => $operation,
                    'idempotency_key' => $idempotencyKey,
                    'status' => IdempotencyKey::STATUS_PROCESSING,
                    'expires_at' => $expiresAt ?? now()->addMinutes($this->ttlMinutes()),
                ]);
            }

            if ($record->status === IdempotencyKey::STATUS_PROCESSING && $record->wasRecentlyCreated === false) {
                return ActionResult::error(
                    message: __('corexis::corexis.idempotency.processing'),
                    code: 'idempotency.processing',
                );
            }

            $result = $callback();

            $record->forceFill([
                'status' => $result->success ? IdempotencyKey::STATUS_COMPLETED : IdempotencyKey::STATUS_FAILED,
                'response_message' => $result->message,
                'response_code' => $result->code,
                'response_data' => $this->serializableData($result->data),
                'response_errors' => $this->serializableErrors($result->errors),
                'completed_at' => now(),
            ])->save();

            return $result;
        });
    }

    private function toActionResult(IdempotencyKey $record): ActionResult
    {
        return new ActionResult(
            success: $record->status === IdempotencyKey::STATUS_COMPLETED,
            message: (string) ($record->response_message ?: __('corexis::corexis.idempotency.replayed')),
            data: $record->response_data ?? [],
            code: $record->response_code,
            errors: $record->response_errors ?? [],
        );
    }

    private function enabled(): bool
    {
        return (bool) config('corexis.idempotency.enabled', true);
    }

    private function table(): string
    {
        return (string) config('corexis.idempotency.table', 'corexis_idempotency_keys');
    }

    private function ttlMinutes(): int
    {
        return max(1, (int) config('corexis.idempotency.ttl_minutes', 1440));
    }

    private function serializableData(mixed $data): mixed
    {
        if ($data instanceof Model) {
            return [
                'model' => $data::class,
                'key' => $data->getKey(),
                'uuid' => data_get($data, 'uuid'),
            ];
        }

        if (is_array($data)) {
            return array_map(fn (mixed $value): mixed => $this->serializableData($value), $data);
        }

        if ($data instanceof JsonSerializable) {
            return $this->serializableData($data->jsonSerialize());
        }

        if (is_object($data)) {
            $properties = get_object_vars($data);

            if ($properties !== []) {
                return array_map(fn (mixed $value): mixed => $this->serializableData($value), $properties);
            }
        }

        return $this->jsonSerializable($data) ? $data : null;
    }

    /**
     * @param  array<mixed>  $errors
     * @return array<mixed>
     */
    private function serializableErrors(array $errors): array
    {
        return $this->jsonSerializable($errors) ? $errors : [];
    }

    private function jsonSerializable(mixed $value): bool
    {
        try {
            json_encode($value, JSON_THROW_ON_ERROR);

            return true;
        } catch (JsonException) {
            return false;
        }
    }
}
