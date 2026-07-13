<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            $column = $model->getUuidColumn();

            if (! $model->getAttribute($column)) {
                $model->setAttribute($column, $model->newUuid());
            }
        });
    }

    public function getUuidColumn(): string
    {
        return 'uuid';
    }

    public function newUuid(): string
    {
        return (string) Str::uuid7();
    }

    public function getRouteKeyName(): string
    {
        return $this->getUuidColumn();
    }
}
