<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Data;

final readonly class ActionResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public array $data = [],
        public ?string $code = null,
    ) {}

    public static function success(string $message = 'Uspješno.', array $data = []): self
    {
        return new self(true, $message, $data);
    }

    public static function error(string $message = 'Došlo je do greške.', ?string $code = null, array $data = []): self
    {
        return new self(false, $message, $data, $code);
    }

    public function failed(): bool
    {
        return ! $this->success;
    }
}
