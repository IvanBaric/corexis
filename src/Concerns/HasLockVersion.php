<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait HasLockVersion
{
    public function getLockVersionColumn(): string
    {
        return defined(static::class.'::LOCK_VERSION_COLUMN')
            ? (string) constant(static::class.'::LOCK_VERSION_COLUMN')
            : 'lock_version';
    }

    public function getLockVersion(): int
    {
        return (int) ($this->getAttribute($this->getLockVersionColumn()) ?? 0);
    }

    public function hasLockVersionColumn(): bool
    {
        return Schema::hasColumn($this->getTable(), $this->getLockVersionColumn());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function saveWithLockVersion(array $attributes, int $expectedLockVersion): bool
    {
        if (! $this->exists || ! $this->hasLockVersionColumn()) {
            $this->fill($attributes)->save();

            return true;
        }

        /** @var Model&self|null $fresh */
        $fresh = $this->newQuery()
            ->whereKey($this->getKey())
            ->lockForUpdate()
            ->first();

        if (! $fresh || $fresh->getLockVersion() !== $expectedLockVersion) {
            return false;
        }

        $fresh->fill($attributes);
        $fresh->setAttribute($fresh->getLockVersionColumn(), $expectedLockVersion + 1);
        $fresh->save();

        $this->setRawAttributes($fresh->getAttributes(), true);
        $this->setRelations($fresh->getRelations());

        return true;
    }
}
