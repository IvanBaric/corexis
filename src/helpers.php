<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Corexis\Support\CurrentActor;
use IvanBaric\Corexis\Support\CurrentLocale;
use IvanBaric\Corexis\Support\CurrentSource;
use IvanBaric\Corexis\Support\CurrentTenant;
use IvanBaric\Corexis\Support\IdempotencyStore;
use IvanBaric\Corexis\Support\ImageUploadPolicy;
use IvanBaric\Corexis\Support\PublicEmptyStatePreview;
use IvanBaric\Corexis\Support\PublicImageOptimizer;

if (! function_exists('corexis_tenant')) {
    function corexis_tenant(): CurrentTenant
    {
        return app(CurrentTenant::class);
    }
}

if (! function_exists('corexis_tenant_id')) {
    function corexis_tenant_id(): int|string|null
    {
        return corexis_tenant()->id();
    }
}

if (! function_exists('corexis_locale')) {
    function corexis_locale(): CurrentLocale
    {
        return app(CurrentLocale::class);
    }
}

if (! function_exists('corexis_locale_code')) {
    function corexis_locale_code(): ?string
    {
        return corexis_locale()->current();
    }
}

if (! function_exists('corexis_actor')) {
    function corexis_actor(): CurrentActor
    {
        return app(CurrentActor::class);
    }
}

if (! function_exists('corexis_actor_id')) {
    function corexis_actor_id(): int|string|null
    {
        return corexis_actor()->id();
    }
}

if (! function_exists('corexis_source')) {
    function corexis_source(): string
    {
        return app(CurrentSource::class)->current();
    }
}

if (! function_exists('corexis_idempotency')) {
    function corexis_idempotency(): IdempotencyStore
    {
        return app(IdempotencyStore::class);
    }
}

if (! function_exists('corexis_image_upload')) {
    function corexis_image_upload(): ImageUploadPolicy
    {
        return app(ImageUploadPolicy::class);
    }
}

if (! function_exists('corexis_validation_toast_message')) {
    function corexis_validation_toast_message(
        ValidationException $exception,
        ?string $fallback = null,
    ): string {
        $fallback ??= __('Provjerite obavezna polja i pokušajte ponovno.');

        foreach ($exception->errors() as $field => $messages) {
            $message = collect($messages)->first();

            if (! is_string($message) || trim($message) === '') {
                continue;
            }

            $message = trim($message);
            $friendlyMessage = corexis_image_upload()->friendlyMessage($message);

            if ($friendlyMessage !== $message || corexis_validation_field_looks_like_upload((string) $field)) {
                return $friendlyMessage;
            }
        }

        return $fallback;
    }
}

if (! function_exists('corexis_validation_field_looks_like_upload')) {
    function corexis_validation_field_looks_like_upload(string $field): bool
    {
        $field = strtolower($field);

        return str_contains($field, 'upload')
            || str_contains($field, 'uploads.')
            || str_contains($field, 'files.')
            || str_contains($field, 'file');
    }
}

if (! function_exists('corexis_public_image_optimizer')) {
    function corexis_public_image_optimizer(): PublicImageOptimizer
    {
        return app(PublicImageOptimizer::class);
    }
}

if (! function_exists('corexis_public_media_disk')) {
    function corexis_public_media_disk(): string
    {
        $disk = config('media-library.disk_name')
            ?: config('gallery.disk')
            ?: config('filesystems.default')
            ?: 'public';

        return is_string($disk) && $disk !== '' ? $disk : 'public';
    }
}

if (! function_exists('corexis_public_media_url')) {
    function corexis_public_media_url(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        $optimizedUrl = corexis_public_image_optimizer()->optimizedUrl($path);

        if ($optimizedUrl !== null) {
            return $optimizedUrl;
        }

        return Storage::disk(corexis_public_media_disk())->url($path);
    }
}

if (! function_exists('corexis_public_empty_state_preview')) {
    function corexis_public_empty_state_preview(): PublicEmptyStatePreview
    {
        return app(PublicEmptyStatePreview::class);
    }
}

if (! function_exists('corexis_can')) {
    function corexis_can(string $ability, mixed $arguments = []): bool
    {
        if (corexis_authorization_missing($ability)) {
            return true;
        }

        return Gate::allows($ability, $arguments);
    }
}

if (! function_exists('corexis_authorize')) {
    function corexis_authorize(string $ability, mixed $arguments = []): void
    {
        if (corexis_authorization_missing($ability)) {
            return;
        }

        Gate::authorize($ability, $arguments);
    }
}

if (! function_exists('corexis_authorization_result')) {
    function corexis_authorization_result(string $ability, mixed $arguments = []): ?ActionResult
    {
        if (corexis_authorization_missing($ability)) {
            return null;
        }

        $response = Gate::inspect($ability, $arguments);

        if ($response->allowed()) {
            return null;
        }

        $message = $response->message() ?: __('corexis::corexis.authorization.denied');

        return ActionResult::error(
            message: $message,
            code: 'authorization.denied',
            errors: [
                'authorization' => [$message],
            ],
        );
    }
}

if (! function_exists('corexis_authorization_missing')) {
    function corexis_authorization_missing(string $ability): bool
    {
        if ((bool) config('corexis.authorization.fail_when_missing', false)) {
            return false;
        }

        if (Gate::has($ability)) {
            return false;
        }

        $user = auth()->user();

        if (! $user || ! method_exists($user, 'hasPermission') || ! str_contains($ability, '.')) {
            return true;
        }

        return ! corexis_authorization_ability_is_registered($ability);
    }
}

if (! function_exists('corexis_authorization_ability_is_registered')) {
    function corexis_authorization_ability_is_registered(string $ability): bool
    {
        try {
            $cacheKey = 'corexis.authorization.registered_abilities';
            $request = ! app()->runningInConsole() && app()->bound('request')
                ? app('request')
                : null;

            if ($request instanceof Request && $request->attributes->has($cacheKey)) {
                /** @var array<string, true> $registeredAbilities */
                $registeredAbilities = $request->attributes->get($cacheKey);

                return isset($registeredAbilities[$ability]);
            }

            $registeredAbilities = Schema::hasTable('permission_items')
                ? DB::table('permission_items')
                    ->pluck('code')
                    ->filter(fn (mixed $code): bool => is_string($code) && $code !== '')
                    ->mapWithKeys(fn (string $code): array => [$code => true])
                    ->all()
                : [];

            $request?->attributes->set($cacheKey, $registeredAbilities);

            return isset($registeredAbilities[$ability]);
        } catch (Throwable) {
            return false;
        }
    }
}
