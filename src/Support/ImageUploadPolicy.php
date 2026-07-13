<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

final class ImageUploadPolicy
{
    /** @return array<int, string> */
    public function rules(bool $nullable = true): array
    {
        $rules = [$nullable ? 'nullable' : 'required', 'image'];

        $mimes = $this->mimes();

        if ($mimes !== []) {
            $rules[] = 'mimes:'.implode(',', $mimes);
        }

        $rules[] = 'max:'.$this->maxFileSizeKb();

        $minWidth = $this->minWidth();
        $minHeight = $this->minHeight();

        if ($minWidth !== null || $minHeight !== null) {
            $dimensions = [];

            if ($minWidth !== null) {
                $dimensions[] = 'min_width='.$minWidth;
            }

            if ($minHeight !== null) {
                $dimensions[] = 'min_height='.$minHeight;
            }

            $rules[] = 'dimensions:'.implode(',', $dimensions);
        }

        return $rules;
    }

    public function maxFileSizeKb(): int
    {
        return max(1, (int) config('corexis.image_uploads.default.max_file_size_kb', 6144));
    }

    public function maxFileSizeMb(): string
    {
        $mb = $this->maxFileSizeKb() / 1024;

        return floor($mb) === $mb
            ? (string) (int) $mb
            : rtrim(rtrim(number_format($mb, 1, ',', ''), '0'), ',');
    }

    /** @return array<int, string> */
    public function mimes(): array
    {
        return array_values(array_filter(
            (array) config('corexis.image_uploads.default.mimes', ['jpg', 'jpeg', 'png', 'webp']),
            static fn (mixed $mime): bool => is_string($mime) && $mime !== '',
        ));
    }

    public function minWidth(): ?int
    {
        $value = config('corexis.image_uploads.default.min_width');

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    public function minHeight(): ?int
    {
        $value = config('corexis.image_uploads.default.min_height');

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    public function helpText(): string
    {
        return __('JPG, PNG ili WebP do :size MB.', ['size' => $this->maxFileSizeMb()]);
    }

    /** @return array<string, string> */
    public function messages(string $field): array
    {
        return [
            $field.'.uploaded' => $this->failedMessage(),
            $field.'.image' => __('Odabrana datoteka mora biti slika.'),
            $field.'.max' => __('Slika je prevelika. Najveća dopuštena veličina je :size MB.', [
                'size' => $this->maxFileSizeMb(),
            ]),
            $field.'.mimes' => __('Format slike nije podržan. Dopušteni formati su: :formats.', [
                'formats' => $this->mimeList(),
            ]),
            $field.'.dimensions' => __('Slika ne zadovoljava minimalne dimenzije.'),
        ];
    }

    public function failedMessage(): string
    {
        return __('Slika se nije mogla prenijeti. Provjerite da je datoteka u podržanom formatu (:formats), manja od :size MB i pokušajte ponovno.', [
            'size' => $this->maxFileSizeMb(),
            'formats' => $this->mimeList(),
        ]);
    }

    public function friendlyMessage(string $message): string
    {
        if (! $this->isGenericUploadFailure($message)) {
            return $message;
        }

        return $this->failedMessage();
    }

    public function mimeList(): string
    {
        return strtoupper(implode(', ', $this->mimes()));
    }

    private function isGenericUploadFailure(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'failed to upload')
            || str_contains($message, 'prijenos polja')
            || str_contains($message, 'nije uspio')
            || str_contains($message, 'uploads.')
            || str_contains($message, 'files.')
            || str_contains($message, 'featuredimageupload')
            || str_contains($message, 'imageupload');
    }
}
