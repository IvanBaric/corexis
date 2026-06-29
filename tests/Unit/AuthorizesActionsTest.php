<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Unit;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use IvanBaric\Corexis\Concerns\AuthorizesActions;
use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Corexis\Tests\TestCase;

class AuthorizesActionsTest extends TestCase
{
    public function test_authorize_action_returns_null_when_allowed(): void
    {
        Gate::define('corexis.allowed', fn ($user = null): bool => true);

        $action = new class {
            use AuthorizesActions;

            public function handle(): ?ActionResult
            {
                return $this->authorizeAction('corexis.allowed');
            }
        };

        $this->assertNull($action->handle());
    }

    public function test_authorize_action_returns_action_result_when_denied(): void
    {
        Gate::define('corexis.denied', fn ($user = null): bool => false);

        $action = new class {
            use AuthorizesActions;

            public function handle(): ?ActionResult
            {
                return $this->authorizeAction('corexis.denied');
            }
        };

        $result = $action->handle();

        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertSame('authorization.denied', $result->code);
        $this->assertSame(__('corexis::corexis.authorization.denied'), $result->message);
    }

    public function test_corexis_can_uses_laravel_gate(): void
    {
        Gate::define('corexis.ui', fn ($user = null): bool => true);

        $this->assertTrue(corexis_can('corexis.ui'));
    }

    public function test_corexis_authorize_throws_when_denied(): void
    {
        Gate::define('corexis.throw', fn ($user = null): bool => false);

        $this->expectException(AuthorizationException::class);

        corexis_authorize('corexis.throw');
    }
}
