<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Concerns;

use IvanBaric\Corexis\Data\ActionResult;

trait AuthorizesActions
{
    protected function authorizeAction(string $ability, mixed $arguments = []): ?ActionResult
    {
        return corexis_authorization_result($ability, $arguments);
    }

    protected function authorizeActionOrFail(string $ability, mixed $arguments = []): void
    {
        corexis_authorize($ability, $arguments);
    }
}
