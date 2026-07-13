# Corexis

Corexis is the central shared infrastructure package for the `ivanbaric/*` Laravel package ecosystem.

It provides common context contracts, default resolvers, wrapper services, helper functions, a shared config file, an `ActionResult` DTO, a tenant-aware model concern, and an install command for generating host-application resolver stubs.

Corexis is not an application package. It does not contain SEO, audit, media, forms, notes, roles, permissions, team membership, UI, or business modules.

Corexis does not depend on Velora. Velora integration is generated only in the host application through an optional stub.

## Installation

```bash
composer require ivanbaric/corexis
```

Publish the config:

```bash
php artisan vendor:publish --tag=corexis-config
```

Or run the installer:

```bash
php artisan corexis:install
```

For a host app that already uses Velora helpers, generate the bridge resolver:

```bash
php artisan corexis:install --velora
```

The installer does not install Velora and does not add Velora to Composer. With `--velora`, it only generates host-app code at `app/Support/Tenancy/CurrentTeamResolver.php`.

## Config

Corexis publishes `config/corexis.php`. Other packages should inherit this config instead of creating separate tenant, locale, actor, or source resolver settings.

Tenancy can be disabled. When disabled, Corexis binds `TenantResolver` to `NullTenantResolver`.

When enabled, configure the resolver:

```php
'tenancy' => [
    'enabled' => true,
    'resolver' => App\Support\Tenancy\CurrentTeamResolver::class,
    'id_column' => 'team_id',
],
```

## ConfigResolver Standard

Packages that expose configurable models, table names, or replaceable infrastructure classes must use the shared Corexis `ConfigResolver` through a package-local resolver such as `BlogConfigResolver`, `PagesConfigResolver`, or `SeoConfigResolver`.

Corexis owns the generic validation:

```php
app(ConfigResolver::class)->model(
    key: 'blog.models.post',
    default: Post::class,
    expectedType: Post::class,
);

app(ConfigResolver::class)->table(
    key: 'blog.tables.posts',
    default: 'blog_posts',
);

app(ConfigResolver::class)->implementation(
    key: 'seo.renderer.class',
    default: HtmlSeoRenderer::class,
    expectedType: SeoRenderer::class,
);
```

Package code should call explicit package methods such as `BlogConfigResolver::postModel()` or `BlogConfigResolver::postsTable()`, not repeat raw `config()` checks in models, migrations, services, providers, or actions.

Invalid configured classes and table names fail fast with `InvalidConfiguration`. A `null` config value uses the package default; an invalid non-null value must not silently fall back.

## Contracts

Packages should depend on contracts, not app-specific helpers:

```php
use IvanBaric\Corexis\Contracts\ActorResolver;
use IvanBaric\Corexis\Contracts\LocaleResolver;
use IvanBaric\Corexis\Contracts\SourceResolver;
use IvanBaric\Corexis\Contracts\TenantResolver;

$tenantId = app(TenantResolver::class)->id();
$locale = app(LocaleResolver::class)->current();
$actorId = app(ActorResolver::class)->id();
$source = app(SourceResolver::class)->current();
```

Do not call `team()`, `current_team_id()`, `auth()->id()`, `app()->getLocale()`, or request source detection directly from other packages. Keep those details behind Corexis resolvers.

## Velora Bridge

The host app can use Velora helpers while packages remain agnostic:

```php
namespace App\Support\Tenancy;

use App\Models\Team;
use IvanBaric\Corexis\Contracts\TenantResolver;

final class CurrentTeamResolver implements TenantResolver
{
    public function enabled(): bool
    {
        return true;
    }

    public function current(): ?Team
    {
        return team();
    }

    public function id(): int|string|null
    {
        return current_team_id();
    }

    public function uuid(): ?string
    {
        return team()?->uuid;
    }

    public function type(): ?string
    {
        return Team::class;
    }
}
```

Corexis does not know what a team is. This class lives in the host application and is only generated when `corexis:install --velora` is used.

## Helpers

Corexis exposes prefixed helpers to avoid collisions with app or Velora helpers:

```php
corexis_tenant();
corexis_tenant_id();
corexis_locale();
corexis_locale_code();
corexis_actor();
corexis_actor_id();
corexis_source();
corexis_image_upload();
corexis_public_empty_state_preview();
```

## Livewire Security

Every Livewire component change should include a security pass before completion. Treat public properties, form state, and action parameters as untrusted browser input. Use `#[Locked]` for server-owned route/model identity, mount-only configuration, pending modal identifiers, and read-only display state. Re-resolve models through tenant-scoped queries and authorize again before writes. See `docs/ecosystem-architecture.md` for the full Livewire security standard.

Host apps should register custom no-argument route middleware with `Livewire::addPersistentMiddleware(...)` so authorization and tenant/team guards are re-applied on later Livewire update requests. Middleware with arguments, such as `permission:*` or `role:*`, still needs explicit authorization inside components, Actions, policies, or domain services before sensitive writes.

Host apps should also put the Livewire update route behind a central named rate limiter, for example `throttle:livewire-updates`, and keep the per-minute limit configurable from host app config.

For Livewire 4 CSP hardening, expose CSP-safe mode through `config('livewire.csp_safe')`, but keep it disabled until local or staging browser testing confirms Alpine and Livewire expressions work without `'unsafe-eval'`. Prefer simple inline expressions and move complex Alpine behavior into methods or `Alpine.data()` modules.

## Auth And Invitations

Login, two-factor, passkey, password reset, and public invitation flows should have explicit server-side rate limiting. Invitation links should use signed URLs, short expiry, hashed tokens, route-level IP throttling, and token-specific preview/submit throttling. Invitation acceptance should re-check status and role assignability inside a locked transaction before creating membership.

## Image Upload Policy

Corexis defines one default image upload policy used across packages:

```php
'image_uploads' => [
    'default' => [
        'max_file_size_kb' => 6144,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'min_width' => null,
        'min_height' => null,
    ],
],
```

Packages should use the shared helper instead of hardcoding upload limits:

```php
corexis_image_upload()->rules();
corexis_image_upload()->helpText();
corexis_image_upload()->maxFileSizeKb();
```

By default, image uploads are limited to 6 MB. Change `config/corexis.php` in the host project when the whole site should allow a different image size.

For public uploaded media, use one disk variable across the host project: `MEDIA_DISK`. Media Library, gallery storage, and `corexis_public_media_disk()` should resolve to that same disk unless the project has a deliberate separate storage boundary.

For admin Livewire image uploads, especially mobile photos and header/hero images, include client-side preparation before the Livewire upload starts: validate image type and size in the browser, resize large mobile photos to a reasonable maximum dimension, compress to a web-friendly format, then upload. This improves mobile reliability and does not replace server-side validation or the optimized Media Library master.

## Public Empty State Preview

Corexis can provide a shared switch for public sections that need to simulate an empty state without deleting tenant content:

```php
'public' => [
    'test_empty_states' => env('COREXIS_PUBLIC_TEST_EMPTY_STATES', false),
],
```

Packages should pass the current tenant/team id and keep the actual empty rendering local to their layout:

```php
corexis_public_empty_state_preview()->enabledForTenant($teamId);
```

The package default is disabled. When enabled, the preview only applies to the authenticated tenant user or a superadmin, while public visitors continue to see normal content.

## Public Empty State Component

Use the shared public empty-state component for public website sections that have no visible content:

```blade
<x-corexis::public-empty-state
    class="cx-public-section-content"
    icon="photo"
    :title="__('Fotografije uskoro')"
    :description="__('Fotografije će se prikazati ovdje kada budu spremne za objavu.')"
/>
```

Do not hand-build one-off public empty-state blocks in section templates. Keep visitor-facing text neutral and avoid admin instructions such as "Dodajte fotografije" on the public frontend.

## Public Image Placeholder Component

Use the shared public image placeholder when a public section has a media slot but the user has not uploaded an image yet:

```blade
<x-corexis::public-image-placeholder
    class="aspect-[4/3] w-full"
    icon="photo"
/>
```

Keep the same aspect ratio, radius and size as the real image. Use content-aware icons such as `photo`, `cube`, `newspaper`, `film` or `heart`. Do not hand-build gray or empty image fallback blocks in public section templates.

## Model Concerns

Corexis provides opt-in model concerns for behavior shared across package models. Models should compose only the concerns they need instead of extending a package-specific base model.

### BelongsToTenant

Use the trait on Eloquent models that belong to the current tenant:

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Concerns\BelongsToTenant;

class Post extends Model
{
    use BelongsToTenant;
}
```

The trait uses `config('corexis.tenancy.id_column')`, which defaults to `team_id` in the IvanBaric ecosystem. It assigns the resolved tenant on create, applies the tenant as a global scope, rejects unresolved enabled tenant contexts, and prevents regular updates from moving a model to another tenant.

Trusted cross-tenant code can select a tenant explicitly:

```php
Post::query()->forTenant($tenantId)->get();
```

`forTenant()` removes the current global tenant scope, so callers must authorize this operation before using it with browser-controlled input.

### HasUuid

Use `HasUuid` when a model keeps its numeric primary key but exposes a UUIDv7 publicly:

```php
use IvanBaric\Corexis\Concerns\HasUuid;

class Post extends Model
{
    use HasUuid;
}
```

The trait assigns a missing UUID on create and returns the UUID column from `getRouteKeyName()`. Override `getUuidColumn()` or `newUuid()` when a model needs a different convention. Keep a unique database index on the UUID column.

### HasUniqueSlug

Use `HasUniqueSlug` for stable slugs generated on create:

```php
use IvanBaric\Corexis\Concerns\HasUniqueSlug;

class Post extends Model
{
    use HasUniqueSlug;

    public function slugSource(): string
    {
        return $this->title;
    }
}
```

Models that also use `BelongsToTenant` are tenant-scoped automatically. Other models can override `uniqueSlugScope()` when uniqueness depends on different columns. Collisions receive deterministic `-1`, `-2`, and later suffixes within the configured scope. Existing slugs remain stable when their source changes; call `regenerateSlug()` explicitly when a URL should change. Keep a matching database unique index, such as `unique(['team_id', 'slug'])`, as the final concurrency safeguard.

Configure a shared slug normalizer when the ecosystem uses Sanigen or another transliterator:

```php
'slug' => [
    'normalizer' => App\Support\SanigenSlugGenerator::class,
    'normalizer_method' => 'generate',
'fallback' => 'record',
],
```

### HasLockVersion

Use `HasLockVersion` on models whose admin forms need optimistic concurrency protection. The trait reads the configurable `lock_version` column and `saveWithLockVersion()` updates only when the submitted version still matches the stored record. A stale write returns `false` instead of silently overwriting a newer change.

Actions can compose `UsesOptimisticLocking` to extract the submitted version, call the model contract and return the standard `conflict.stale_model` `ActionResult` on conflict.

### AuthorizesActions

Write Actions can use `AuthorizesActions` for the shared Corexis authorization boundary:

```php
if ($result = $this->authorizeAction('posts.update', $post)) {
    return $result;
}
```

Authorization remains server-side even when the UI hides an unavailable action. Package-specific authorization concerns may add tenant or domain checks, but should delegate the final ability decision to Corexis instead of reading authentication state directly.

## ActionResult

`ActionResult` is a small DTO for actions, Livewire flows, toasts, and package APIs:

```php
use IvanBaric\Corexis\Data\ActionResult;

return ActionResult::success('Saved.', ['id' => $model->id]);

return ActionResult::error('Could not save.', 'validation_failed', [
    'field' => 'name',
]);
```

It exposes `success`, `message`, `data`, `code`, and `errors`. The third positional argument remains legacy-compatible `data`; new code should use named arguments when returning validation or business-rule errors:

```php
return ActionResult::error(
    message: __('Provjerite unesene podatke.'),
    code: 'validation_failed',
    errors: ['name' => [__('Naziv je obavezan.')]],
);
```

## Idempotency

Corexis includes a small idempotency store for retryable external writes such as payments, webhooks, API writes, and queue jobs.

Run the Corexis migrations to create the `corexis_idempotency_keys` table. Packages can then wrap ActionResult-based work:

```php
return corexis_idempotency()->run(
    scope: 'billing',
    operation: 'payment_attempt.confirm',
    idempotencyKey: $request->header('Idempotency-Key'),
    callback: fn (): ActionResult => $action->handle(...),
);
```

The first request runs the callback and stores a safe result summary. A repeated request with the same `scope`, `operation`, and key returns the stored `ActionResult` without running the callback or dispatching success events again.

Use stable keys such as provider event IDs, webhook IDs, payment IDs, or client-generated UUIDs. Do not store secrets or whole model payloads as idempotency result data.

## Domain Events

Domain events can implement `IvanBaric\Corexis\Contracts\Events\DomainEvent` as a shared marker for package listeners and subscribers.

The ecosystem write flow is:

```text
Livewire Component -> Livewire Form Object -> Action -> ActionResult -> Domain Event -> Listener
```

See `docs/action-result-events.md` for the event and `ActionResult` standard.

See `docs/ecosystem-architecture.md` for the full IvanBaric package architecture standard, package boundaries, Action rules, Form Object rules, and listener-based integration pattern.

See `docs/public-ui-typography.md` for the public Tailwind section typography standard used by reusable website layouts.

See `docs/public-ui-motion.md` for the public motion standard, including the first-load public tenant loader pattern.

Host applications can include the shared public typography classes from Corexis:

```css
@import '../../vendor/ivanbaric/corexis/resources/css/public-typography.css';
```

## Package Integration

Other packages can expose inheritance settings:

```php
'tenant' => [
    'mode' => 'inherit',
],

'locale' => [
    'mode' => 'inherit',
],

'actor' => [
    'mode' => 'inherit',
],
```

In inherited mode, packages use Corexis global context. This keeps tenant, locale, actor, and source resolution consistent across audit, SEO, media, forms, notes, legal, notifications, and future packages.
