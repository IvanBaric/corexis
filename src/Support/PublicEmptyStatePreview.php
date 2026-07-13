<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Model;

final readonly class PublicEmptyStatePreview
{
    public function __construct(
        private AuthFactory $auth,
    ) {}

    public function enabledForTenant(Model|int|string|null $tenant = null, mixed $user = null): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $user ??= $this->auth->guard()->user();

        if (! $user) {
            return false;
        }

        if ($this->userCanBypassTenantCheck($user)) {
            return true;
        }

        $tenantId = $this->tenantId($tenant);

        if ($tenantId === null) {
            return false;
        }

        $userTenantId = data_get($user, 'current_team_id');

        return $userTenantId !== null && (string) $userTenantId === (string) $tenantId;
    }

    public function enabledForTeam(Model|int|string|null $team = null, mixed $user = null): bool
    {
        return $this->enabledForTenant($team, $user);
    }

    public function url(?string $url): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        return $url;
    }

    public function enabled(): bool
    {
        return (bool) config('corexis.public.test_empty_states', false);
    }

    private function userCanBypassTenantCheck(mixed $user): bool
    {
        return (bool) data_get($user, 'is_superadmin');
    }

    private function tenantId(Model|int|string|null $tenant): int|string|null
    {
        if ($tenant instanceof Model) {
            return $tenant->getKey();
        }

        return $tenant;
    }
}
