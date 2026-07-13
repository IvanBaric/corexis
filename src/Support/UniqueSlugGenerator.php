<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use Illuminate\Database\Eloquent\Model;

final class UniqueSlugGenerator
{
    public function __construct(
        private readonly SlugNormalizer $normalizer,
    ) {}

    public function generate(Model $model, string $source): string
    {
        $base = $this->normalizer->normalize($source);
        $candidate = $base;
        $suffix = 0;

        while ($this->exists($model, $candidate)) {
            $candidate = $base.'-'.++$suffix;
        }

        return $candidate;
    }

    private function exists(Model $model, string $slug): bool
    {
        $query = $model->newQueryWithoutScopes()
            ->where($model->getSlugColumn(), $slug);

        foreach ($model->uniqueSlugScope() as $column => $value) {
            $value === null
                ? $query->whereNull($column)
                : $query->where($column, $value);
        }

        if ($model->exists) {
            $query->whereKeyNot($model->getKey());
        }

        return $query->exists();
    }
}
