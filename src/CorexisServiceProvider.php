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
use IvanBaric\Corexis\Support\ConfigResolver;
use IvanBaric\Corexis\Support\Corexis;
use IvanBaric\Corexis\Support\CorexisConfigResolver;
use IvanBaric\Corexis\Support\CurrentActor;
use IvanBaric\Corexis\Support\CurrentLocale;
use IvanBaric\Corexis\Support\CurrentSource;
use IvanBaric\Corexis\Support\CurrentTenant;
use IvanBaric\Corexis\Support\IdempotencyStore;
use IvanBaric\Corexis\Support\ImageUploadPolicy;
use IvanBaric\Corexis\Support\PublicEmptyStatePreview;
use IvanBaric\Corexis\Support\SlugNormalizer;
use IvanBaric\Corexis\Support\UniqueSlugGenerator;

class CorexisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/corexis.php', 'corexis');

        $this->app->singleton(
            ConfigResolver::class,
            fn ($app): ConfigResolver => new ConfigResolver($app['config']),
        );

        $this->app->bind(TenantResolver::class, function ($app): TenantResolver {
            if (! (bool) $app['config']->get('corexis.tenancy.enabled', false)) {
                return $app->make(NullTenantResolver::class);
            }

            return $app->make(CorexisConfigResolver::tenantResolver());
        });

        $this->app->bind(LocaleResolver::class, fn ($app): LocaleResolver => $app->make(
            CorexisConfigResolver::localeResolver()
        ));

        $this->app->bind(ActorResolver::class, fn ($app): ActorResolver => $app->make(
            CorexisConfigResolver::actorResolver()
        ));

        $this->app->bind(SourceResolver::class, fn ($app): SourceResolver => $app->make(
            CorexisConfigResolver::sourceResolver()
        ));

        $this->app->bind(CurrentTenant::class);
        $this->app->bind(CurrentLocale::class);
        $this->app->bind(CurrentActor::class);
        $this->app->bind(CurrentSource::class);
        $this->app->singleton(ImageUploadPolicy::class);
        $this->app->singleton(IdempotencyStore::class);
        $this->app->singleton(PublicEmptyStatePreview::class);
        $this->app->singleton(SlugNormalizer::class);
        $this->app->singleton(UniqueSlugGenerator::class);
        $this->app->singleton(Corexis::class);
    }

    public function boot(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components');
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'corexis');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'corexis');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/corexis.php' => config_path('corexis.php'),
        ], 'corexis-config');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/corexis'),
        ], 'corexis-translations');

        $this->publishes([
            __DIR__.'/../resources/css/public-typography.css' => resource_path('css/vendor/corexis-public-typography.css'),
        ], 'corexis-public-typography');

        $this->publishes([
            __DIR__.'/../resources/css/public-surfaces.css' => resource_path('css/vendor/corexis-public-surfaces.css'),
        ], 'corexis-public-surfaces');

        $this->publishes([
            __DIR__.'/../resources/css/public-backgrounds.css' => resource_path('css/vendor/corexis-public-backgrounds.css'),
        ], 'corexis-public-backgrounds');

        $this->publishes([
            __DIR__.'/../resources/css/public-motion.css' => resource_path('css/vendor/corexis-public-motion.css'),
        ], 'corexis-public-motion');

        $this->publishes([
            __DIR__.'/../resources/css/public-containers.css' => resource_path('css/vendor/corexis-public-containers.css'),
        ], 'corexis-public-containers');

        $this->publishes([
            __DIR__.'/../resources/css/public-spacing.css' => resource_path('css/vendor/corexis-public-spacing.css'),
        ], 'corexis-public-spacing');

        $this->publishes([
            __DIR__.'/../resources/css/public-icons.css' => resource_path('css/vendor/corexis-public-icons.css'),
        ], 'corexis-public-icons');

        $this->publishes([
            __DIR__.'/../resources/css/public-badges.css' => resource_path('css/vendor/corexis-public-badges.css'),
        ], 'corexis-public-badges');

        $this->publishes([
            __DIR__.'/../resources/css/public-borders.css' => resource_path('css/vendor/corexis-public-borders.css'),
        ], 'corexis-public-borders');

        $this->commands([
            InstallCorexisCommand::class,
        ]);
    }
}
