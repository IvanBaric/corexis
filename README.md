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
```

## BelongsToTenant

Use the trait on Eloquent models that should receive the current tenant id on create:

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Concerns\BelongsToTenant;

class Post extends Model
{
    use BelongsToTenant;
}
```

The trait uses `config('corexis.tenancy.id_column')`, which defaults to `team_id` in the IvanBaric ecosystem. It does not overwrite an existing tenant value, and does not add a global scope by default.

To query explicitly:

```php
Post::query()->forCurrentTenant()->get();
```

If `corexis.tenancy.fail_when_unresolved` is `true`, the trait throws `TenantNotResolvedException` when tenancy is enabled but no tenant id is available.

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

## Domain Events

Domain events can implement `IvanBaric\Corexis\Contracts\Events\DomainEvent` as a shared marker for package listeners and subscribers.

The ecosystem write flow is:

```text
Livewire Component -> Livewire Form Object -> Action -> ActionResult -> Domain Event -> Listener
```

See `docs/action-result-events.md` for the event and `ActionResult` standard.

See `docs/ecosystem-architecture.md` for the full IvanBaric package architecture standard, package boundaries, Action rules, Form Object rules, and listener-based integration pattern.

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
