<?php

declare(strict_types=1);

namespace IvanBaric\Corexis;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use IvanBaric\Corexis\Console\InstallCorexisCommand;
use IvanBaric\Corexis\Contracts\ActorResolver;
use IvanBaric\Corexis\Contracts\LocaleResolver;
use IvanBaric\Corexis\Contracts\SourceResolver;
use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Corexis\Resolvers\NullTenantResolver;
use IvanBaric\Corexis\Support\Corexis;
use IvanBaric\Corexis\Support\CurrentActor;
use IvanBaric\Corexis\Support\CurrentLocale;
use IvanBaric\Corexis\Support\CurrentSource;
use IvanBaric\Corexis\Support\CurrentTenant;

class CorexisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/corexis.php', 'corexis');

        $this->app->bind(TenantResolver::class, function ($app): TenantResolver {
            if (! (bool) $app['config']->get('corexis.tenancy.enabled', false)) {
                return $app->make(NullTenantResolver::class);
            }

            return $app->make($app['config']->get('corexis.tenancy.resolver', NullTenantResolver::class));
        });

        $this->app->bind(LocaleResolver::class, fn ($app): LocaleResolver => $app->make(
            $app['config']->get('corexis.locale.resolver')
        ));

        $this->app->bind(ActorResolver::class, fn ($app): ActorResolver => $app->make(
            $app['config']->get('corexis.actor.resolver')
        ));

        $this->app->bind(SourceResolver::class, fn ($app): SourceResolver => $app->make(
            $app['config']->get('corexis.source.resolver')
        ));

        $this->app->bind(CurrentTenant::class);
        $this->app->bind(CurrentLocale::class);
        $this->app->bind(CurrentActor::class);
        $this->app->bind(CurrentSource::class);
        $this->app->singleton(Corexis::class);
    }

    public function boot(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'corexis');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/corexis.php' => config_path('corexis.php'),
        ], 'corexis-config');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/corexis'),
        ], 'corexis-translations');

        $this->commands([
            InstallCorexisCommand::class,
        ]);
    }
}
