<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Concerns;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Support\UniqueSlugGenerator;

trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::creating(function (Model $model): void {
            $slug = $model->getAttribute($model->getSlugColumn());

            $model->regenerateSlug(is_string($slug) && trim($slug) !== '' ? $slug : null);
        });

        static::updating(function (Model $model): void {
            if (! $model->isDirty($model->getSlugColumn())) {
                return;
            }

            $slug = $model->getAttribute($model->getSlugColumn());

            $model->regenerateSlug(is_string($slug) && trim($slug) !== '' ? $slug : null);
        });
    }

    abstract public function slugSource(): string;

    public function getSlugColumn(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, int|string|null>
     */
    public function uniqueSlugScope(): array
    {
        if (method_exists($this, 'getTenantColumn')) {
            $column = $this->getTenantColumn();

            return [$column => $this->getAttribute($column)];
        }

        return [];
    }

    public function regenerateSlug(?string $source = null): static
    {
        $this->setAttribute(
            $this->getSlugColumn(),
            app(UniqueSlugGenerator::class)->generate($this, $source ?? $this->slugSource()),
        );

        return $this;
    }
}
