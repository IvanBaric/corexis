# Corexis

Corexis is the central shared infrastructure package for the `ivanbaric/*` Laravel package ecosystem.

It provides common context contracts, default resolvers, wrapper services, helper functions, a shared config file, an `ActionResult` DTO, a tenant-aware model concern, and an install command for generating host-application resolver stubs.

Corexis is not an application package. It does not contain SEO, audit, media, forms, notes, roles, permissions, team membership, UI, or business modules.

Corexis does not depend on Velora. Velora integration is generated only in the host application through an optional stub.

## Documentation

This README documents installation and package APIs. Normative engineering and product standards live in the [Corexis agent documentation index](AGENTS.md), which routes work to the relevant documents without requiring every standard to be loaded for every task.

Use the [ecosystem architecture](docs/ecosystem-architecture.md) for package boundaries and write-flow rules. Project-specific workflows, including Niva deployment and package repository synchronization, live under [project profiles](docs/projects/niva.md).

Reusable product foundations extracted from host applications are documented separately:

- [Reusable public site](docs/reusable-public-site.md): public subject resolution, Pages controllers, content providers, management UI and Template Engine boundaries.
- [Reusable onboarding](docs/reusable-onboarding.md): persistent guided setup, host adapters, AI processing, completion guards and migration from legacy settings.

These documents define ownership across Pages, Template Engine, Onboarding, Velora, Gallery and Audit so new products can assemble the same foundation without copying controllers or workflows from an existing application.

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

## Framework Defaults

Registering Corexis also applies shared Laravel defaults that should be consistent across host applications:

- immutable dates through `CarbonImmutable`;
- Eloquent strict mode outside production;
- destructive database command protection in production;
- a 500 ms cumulative database query-time warning without SQL or bindings in logs;
- production password rules with a minimum of eight characters, mixed case, letters, numbers, symbols, and the uncompromised check;
- translated short messages for required validation rules;
- prevention of unfaked Laravel HTTP client requests during tests.

These defaults can be changed centrally under `corexis.framework` in `config/corexis.php`. A `null` value for `strict_models` or `prohibit_destructive_commands` keeps the environment-aware defaults. Set `cumulative_query_time_threshold_ms` to `0` to disable query-time warnings.

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

Storage selection, Media Library optimization, conversion, client-side preparation, and lazy-loading requirements are defined in the [storage and media standard](docs/standards/storage-media.md) and the detailed [public media standard](docs/public-ui-media.md).

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

Empty-state behavior and visitor-facing copy are defined in the [public UI standard](docs/standards/public-ui.md).

## Public Image Placeholder Component

Use the shared public image placeholder when a public section has a media slot but the user has not uploaded an image yet:

```blade
<x-corexis::public-image-placeholder
    class="aspect-[4/3] w-full"
    icon="photo"
/>
```

Placeholder aspect ratios, radius, icons, and fallback behavior are defined in the [public UI](docs/standards/public-ui.md) and [public media](docs/public-ui-media.md) standards.

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

See the [ActionResult and domain events standard](docs/action-result-events.md) and [ecosystem architecture](docs/ecosystem-architecture.md) for the write flow, package boundaries, Actions, Form Objects, and listener-based integrations.

Public UI implementation rules are indexed in [Corexis AGENTS.md](AGENTS.md).

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

## Development

Run the documentation integrity check and PHPUnit suite together:

```bash
composer test
```

Run only the documentation check:

```bash
composer docs:check
```
