# IvanBaric Package Ecosystem Architecture

Corexis is the shared infrastructure foundation for IvanBaric Laravel packages.

This document defines the package architecture standard. It is intentionally package-level documentation and does not depend on any host application.

## Standard Write Flow

```text
Livewire Component -> Livewire Form Object -> Action -> Corexis ActionResult -> Domain Event -> Package Listener
```

Responsibilities:

- Livewire Component: UI coordination, modal state, action calls, toast/display handling, redirects, list refreshes.
- Livewire Form Object: public form state, validation rules, labels, `fillFromModel()`, `data()`, optional `resetForm()`.
- Action: business operation, authorization, business validation, persistence, transactions, result, success event.
- ActionResult: expected operation outcome returned to UI/API/package consumers.
- Domain Event: small fact that a successful operation happened.
- Listener: side effects, integrations, cache invalidation, audit records, sitemap refreshes, notifications.

## Core Package Rules

Corexis may provide:

- shared DTOs such as `IvanBaric\Corexis\Data\ActionResult`
- marker contracts such as `IvanBaric\Corexis\Contracts\Events\DomainEvent`
- shared resolver contracts for tenant, actor, locale, and source context
- model-agnostic concerns and helpers
- architecture documentation

Corexis must not depend on:

- application models
- teams/users from a host app
- Velora, Pages, Blog, Gallery, SEO, Audit, Billing, or other domain packages
- package-specific database tables
- UI packages

## Action Standard

Meaningful state-changing operations should go through an Action in `src/Actions`.

Examples:

- create
- update
- delete
- sync
- attach
- detach
- reorder
- activate
- suspend
- revoke
- accept
- switch
- upload
- confirm
- reset
- clear

Actions should expose:

```php
public function handle(...): ActionResult
```

Actions should:

- accept typed arguments
- authorize where needed using policies or `Gate::inspect()`
- use Corexis authorization helpers/traits to keep Action authorization consistent
- validate business rules where needed
- treat Livewire public properties and action parameters as untrusted input
- use `DB::transaction()` for multi-step writes
- return `IvanBaric\Corexis\Data\ActionResult`
- dispatch a domain event only after success
- throw unexpected programming/system exceptions instead of hiding them as expected failures

Actions must not:

- contain UI logic
- call Flux UI
- use Livewire state
- create toasts
- redirect
- flash session messages
- directly call unrelated packages

Bad:

```php
CreatePageAction directly calls Audit::record();
CreatePageAction directly calls Seo::sync();
CreatePageAction directly calls Gallery::cleanup();
```

Good:

```php
CreatePageAction saves the page.
CreatePageAction dispatches PageCreated.
Audit listens to PageCreated.
SEO listens to PageCreated or PageUpdated.
Gallery listens where media cleanup is relevant.
```

## Domain Event Standard

Domain events should be:

- final where safe
- readonly where safe
- typed
- small
- serializable
- Laravel compatible
- dispatched only after successful persistence

Recommended shape:

```php
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use IvanBaric\Corexis\Contracts\Events\DomainEvent;

final readonly class PageCreated implements DomainEvent, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int|string $pageKey,
        public string $uuid,
    ) {}
}
```

Prefer required identifiers and models only when useful. Avoid payload bloat.

## Livewire Form Object Standard

Packaged Livewire components with non-trivial forms should use Livewire Form Objects.

Form objects belong in `src/Livewire/Forms` or the package's existing Livewire namespace convention.

Form objects should contain:

- public form properties
- validation rules
- validation attributes
- `fillFromModel(...)`
- `data(): array`
- optional `resetForm()`

Form objects must not:

- perform complex business writes
- dispatch domain events
- call unrelated packages
- contain Flux UI logic

## Package Boundaries

Package responsibilities:

- `corexis`: shared contracts, context resolvers, ActionResult, DomainEvent marker, architecture docs.
- `admin-ui`: presentational Blade/UI primitives only.
- `starter`: installer/orchestrator only.
- `velora`: teams, memberships, RBAC, invitations.
- `plan`: plans, entitlements, usage.
- `billing`: subscriptions, payment attempts, access lifecycle.
- `audit`: event/audit listener package.
- `seo`: SEO metadata, sitemap, schema, SEO cache.
- `gallery`: media and gallery management.
- `taxonomy`: reusable categorization.
- `status`: reusable status state and history.
- `eav`: dynamic attributes.
- `meta`: small polymorphic metadata.
- `settings`: configurable settings pages.
- `language`: locale and language management.
- `sanigen`: sanitization/generation utility.
- `pages`: content pages.
- `blog`: posts, news, event and content entries.
- `template-engine`: template registration, schema, render foundation, template payload save action.

## Special Package Notes

`admin-ui` must stay presentational. Do not add queries, validation, permissions, model saves, package-specific row actions, Actions, Events, or Listeners.

`starter` must stay an installer/orchestrator. It may clone packages, configure Composer repositories, require packages, publish resources, run migrations, and run sync commands. It must not become a service layer.

`sanigen` is a low-level utility. Do not add fake domain events or UI just for consistency.

Small low-level packages such as `meta` and `eav` should keep trait APIs stable. Actions are wrappers for meaningful writes, not a reason to over-engineer the package.

## Authorization Standard

Every state-changing Action should make an explicit authorization decision before writing data.

Preferred Action pattern:

```php
use IvanBaric\Corexis\Concerns\AuthorizesActions;

final class UpdatePageAction
{
    use AuthorizesActions;

    public function handle(Page $page, array $data): ActionResult
    {
        if ($result = $this->authorizeAction('pages.update', $page)) {
            return $result;
        }

        // write...
    }
}
```

Corexis provides:

- `corexis_authorization_result($ability, $arguments)`: returns `null` when allowed or `ActionResult::error(...)` when denied.
- `corexis_authorize($ability, $arguments)`: Laravel-native hard authorization that throws a 403 authorization exception.
- `corexis_can($ability, $arguments)`: boolean helper for UI visibility and disabled states.
- `IvanBaric\Corexis\Concerns\AuthorizesActions`: Action trait for `ActionResult`-based authorization.

Use `corexis_authorize()` or route middleware for direct page access. Use `authorizeAction()` inside write Actions. Livewire button visibility is only UX and must not be the only protection.

Package permission definitions should use stable machine codes and translatable labels:

```php
'permissions' => [
    [
        'name' => 'pages',
        'label' => 'pages::permissions.group',
        'description' => 'pages::permissions.description',
        'icon' => 'file-text',
        'items' => [
            ['slug' => 'update', 'code' => 'pages.update', 'label' => 'pages::permissions.update'],
        ],
    ],
],
```

When Velora is installed, its roles and permissions layer can answer Laravel Gate checks for these permission codes. Packages should still call Laravel Gate/Corexis authorization helpers and must not call Velora directly.

For backwards compatibility, Corexis treats a dotted permission ability as missing until it is registered in the permission store, such as Velora's `permission_items.code`. This keeps packages usable before a host app runs permission synchronization. After synchronization, the same `corexis_authorize()` and `authorizeAction()` calls become strict authorization checks.

## Compatibility

## Tenancy Standard

IvanBaric packages use Corexis tenancy as the single shared tenant abstraction:

- `IvanBaric\Corexis\Contracts\TenantResolver`
- `IvanBaric\Corexis\Support\CurrentTenant`
- `IvanBaric\Corexis\Concerns\BelongsToTenant`
- `corexis_tenant_id()`

The ecosystem default tenant column is `team_id`:

```php
'tenancy' => [
    'enabled' => true,
    'resolver' => App\Support\Tenancy\CurrentTeamResolver::class,
    'id_column' => 'team_id',
    'fail_when_unresolved' => false,
],
```

Host applications may use team language in their resolver class names because the app owns the concrete tenant model. Packages should keep using Corexis tenant language and must not call app-specific `team()`, `current_team_id()`, or package-local tenant helpers directly.

Existing package-local tenant/team resolvers may stay as backwards-compatible adapters, but new package code should read tenant context from Corexis.

## Locale Standard

IvanBaric packages use Corexis locale context as the shared locale abstraction:

- `IvanBaric\Corexis\Contracts\LocaleResolver`
- `IvanBaric\Corexis\Support\CurrentLocale`
- `corexis_locale_code()`

Packages should read the active locale through Corexis and use the host app locale only as a fallback. Direct `app()->getLocale()` usage should be limited to the Corexis default resolver and the `language` package where locale switching is the package responsibility.

Visible package text, validation labels, ActionResult messages, command output, mail copy, and admin UI labels should use Laravel translations:

```php
__('package::file.key')
```

or `trans_choice()` for pluralized text. Package Blade views should not contain hardcoded visible admin copy except translation keys or already-translated dynamic values.

Packages with visible text or package-level result messages should ship namespaced translation files in at least:

```text
lang/en/*.php
lang/hr/*.php
```

or `resources/lang` when that is the package's existing convention. Croatian translation files must stay UTF-8 encoded without BOM.

Where package-local `ActionResult` classes already exist, keep compatibility adapters before changing public return types.

Use a separate major or explicitly documented compatibility pass before:

- replacing public return types with Corexis `ActionResult`
- renaming Composer package names
- renaming route names
- renaming database tables
- changing migration history
- changing Livewire major versions
- changing Tailwind major versions

## Target Stack

Preferred baseline where possible:

- PHP 8.2+
- Laravel 11 compatibility
- Laravel 12/13 compatibility where package already supports it
- Livewire 3 for packaged UI
- Tailwind 3
- Flux UI for packaged admin forms

Do not upgrade to Livewire 4 or Tailwind 4 as part of this standard. Do not force PHP 8.3-only unless a package already explicitly requires it and there is a strong reason.
