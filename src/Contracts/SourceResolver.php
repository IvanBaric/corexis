<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Contracts;

interface SourceResolver
{
    public function current(): string;

    /**
     * @return array<int, string>
     */
    public function allowed(): array;

    public function isAllowed(string $source): bool;
}
