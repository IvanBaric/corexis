<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use Illuminate\Support\Str;
use LogicException;

final class SlugNormalizer
{
    public function normalize(string $source): string
    {
        $normalizer = config('corexis.slug.normalizer');

        if ($normalizer === null || $normalizer === '') {
            return $this->fallback($source);
        }

        if (! is_string($normalizer) || ! class_exists($normalizer)) {
            throw new LogicException('The configured Corexis slug normalizer is invalid.');
        }

        $method = (string) config('corexis.slug.normalizer_method', 'generate');
        $instance = app($normalizer);

        if (! is_callable([$instance, $method])) {
            throw new LogicException(sprintf(
                'The configured Corexis slug normalizer [%s] does not provide [%s].',
                $normalizer,
                $method,
            ));
        }

        $slug = trim((string) $instance->{$method}($source));

        return $slug !== '' ? $slug : $this->fallback($source);
    }

    private function fallback(string $source): string
    {
        $slug = Str::slug($source);

        return $slug !== ''
            ? $slug
            : (string) config('corexis.slug.fallback', 'record');
    }
}
