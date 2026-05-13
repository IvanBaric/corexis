<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Unit;

use Illuminate\Support\Facades\Auth;
use IvanBaric\Corexis\Resolvers\AppLocaleResolver;
use IvanBaric\Corexis\Resolvers\AuthActorResolver;
use IvanBaric\Corexis\Resolvers\NullTenantResolver;
use IvanBaric\Corexis\Resolvers\RequestSourceResolver;
use IvanBaric\Corexis\Tests\Fixtures\Models\User;
use IvanBaric\Corexis\Tests\TestCase;

class ResolversTest extends TestCase
{
    public function test_null_tenant_resolver_returns_null_values(): void
    {
        $resolver = new NullTenantResolver;

        $this->assertFalse($resolver->enabled());
        $this->assertNull($resolver->current());
        $this->assertNull($resolver->id());
        $this->assertNull($resolver->uuid());
        $this->assertNull($resolver->type());
    }

    public function test_app_locale_resolver_returns_app_locale(): void
    {
        app()->setLocale('hr');
        config()->set('corexis.locale.default', 'en');
        config()->set('corexis.locale.fallback', 'en');

        $resolver = new AppLocaleResolver;

        $this->assertSame('hr', $resolver->current());
        $this->assertSame('en', $resolver->default());
        $this->assertSame(['hr', 'en'], $resolver->available());
    }

    public function test_auth_actor_resolver_returns_authenticated_user(): void
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Auth::login($user);

        $resolver = new AuthActorResolver;

        $this->assertTrue($resolver->enabled());
        $this->assertTrue($resolver->current()->is($user));
        $this->assertSame($user->getKey(), $resolver->id());
        $this->assertSame(User::class, $resolver->type());
    }

    public function test_request_source_resolver_returns_console_in_console_context(): void
    {
        $this->assertSame('console', (new RequestSourceResolver)->current());
    }
}
