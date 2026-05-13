<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Contracts;

interface LocaleResolver
{
    public function enabled(): bool;

    public function current(): ?string;

    public function default(): ?string;

    public function fallback(): ?string;

    /**
     * @return array<int, string>
     */
    public function available(): array;
}
