<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use IvanBaric\Corexis\Support\PublicUrl;

final readonly class SafePublicUrl implements ValidationRule
{
    public function __construct(private ?PublicUrl $publicUrl = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $publicUrl = $this->publicUrl ?? app(PublicUrl::class);

        if ($publicUrl->sanitize($value) === null) {
            $fail(__('URL nije valjan ili nije dopušten.'));
        }
    }
}
