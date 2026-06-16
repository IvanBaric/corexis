<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Data;

final readonly class ActionResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public mixed $data = [],
        public ?string $code = null,
        public array $errors = [],
    ) {}

    public static function success(string $message = 'Uspješno.', mixed $data = [], ?string $code = null, array $errors = []): self
    {
        return new self(true, $message, $data, $code, $errors);
    }

    public static function error(string $message = 'Došlo je do greške.', ?string $code = null, mixed $data = [], array $errors = []): self
    {
        return new self(false, $message, $data, $code, $errors);
    }

    public function failed(): bool
    {
        return ! $this->success;
    }
}
