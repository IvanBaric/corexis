<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class IdempotencyKey extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'scope',
        'operation',
        'idempotency_key',
        'status',
        'response_message',
        'response_code',
        'response_data',
        'response_errors',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'response_data' => 'array',
            'response_errors' => 'array',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        self::creating(function (self $key): void {
            $key->uuid ??= (string) Str::uuid();
        });
    }

    public function getTable(): string
    {
        return (string) config('corexis.idempotency.table', 'corexis_idempotency_keys');
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
