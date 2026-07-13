<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use Illuminate\Support\Facades\Storage;

final class PublicImageOptimizer
{
    /** @return array<string, string> */
    public function sources(?string $path): array
    {
        if (! $this->isOptimizablePath($path)) {
            return [];
        }

        $sources = [];

        foreach (['avif', 'webp'] as $format) {
            $variant = $this->variantPath($path, $format);

            if ($variant !== null && Storage::disk(corexis_public_media_disk())->exists($variant)) {
                $sources[$format] = Storage::disk(corexis_public_media_disk())->url($variant);
            }
        }

        $fallback = Storage::disk(corexis_public_media_disk())->url($path);

        $sources['fallback'] = $fallback;

        return $sources;
    }

    public function optimizeStoredImage(?string $path): void
    {
        if (! $this->isOptimizablePath($path)) {
            return;
        }

        $disk = Storage::disk(corexis_public_media_disk());

        if (! $disk->exists($path)) {
            return;
        }

        $contents = $disk->get($path);

        if (! is_string($contents) || $contents === '') {
            return;
        }

        $image = @imagecreatefromstring($contents);

        if (! $image) {
            return;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        try {
            $this->writeVariant($image, $path, 'webp', 82);
            $this->writeVariant($image, $path, 'avif', 50);
        } finally {
            imagedestroy($image);
        }
    }

    public function optimizedUrl(?string $path): ?string
    {
        if (! $this->isOptimizablePath($path)) {
            return null;
        }

        $disk = Storage::disk(corexis_public_media_disk());
        $accept = (string) request()->headers->get('Accept', '');
        $formats = [];

        if (str_contains($accept, 'image/avif')) {
            $formats[] = 'avif';
        }

        if (str_contains($accept, 'image/webp')) {
            $formats[] = 'webp';
        }

        foreach ($formats as $format) {
            $variant = $this->variantPath($path, $format);

            if ($variant !== null && $disk->exists($variant)) {
                return $disk->url($variant);
            }
        }

        return null;
    }

    public function variantPath(?string $path, string $format): ?string
    {
        if (! $this->isOptimizablePath($path) || ! in_array($format, ['webp', 'avif'], true)) {
            return null;
        }

        return preg_replace('/\.[^.\/\\\\]+$/', '.'.$format, (string) $path) ?: null;
    }

    private function writeVariant(\GdImage $image, string $path, string $format, int $quality): void
    {
        $variant = $this->variantPath($path, $format);

        if ($variant === null) {
            return;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'corexis-image-');

        if ($tmp === false) {
            return;
        }

        try {
            $written = match ($format) {
                'avif' => function_exists('imageavif') && @imageavif($image, $tmp, $quality),
                'webp' => function_exists('imagewebp') && @imagewebp($image, $tmp, $quality),
                default => false,
            };

            if (! $written || ! is_file($tmp) || filesize($tmp) === 0) {
                return;
            }

            Storage::disk(corexis_public_media_disk())->put($variant, file_get_contents($tmp) ?: '', [
                'visibility' => 'public',
            ]);
        } finally {
            @unlink($tmp);
        }
    }

    private function isOptimizablePath(?string $path): bool
    {
        if (! is_string($path) || $path === '') {
            return false;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    }
}
