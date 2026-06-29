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
- reusable package and public UI standards documentation

Corexis must not depend on:

- application models
- teams/users from a host app
- Velora, Pages, Blog, Gallery, SEO, Audit, Billing, or other domain packages
- package-specific database tables
- UI packages

## Public UI Typography Standard

Reusable public website layouts should follow the Corexis public Tailwind typography roles documented in `docs/public-ui-typography.md`.

The standard rule is: choose text size by semantic role, not by individual layout variation. Section titles, section descriptions, item titles, item descriptions, meta text, lead text, CTA text, and featured titles should keep consistent Tailwind classes across comparable layout variants.

Do not introduce a new text size in a package layout unless it represents a new documented text role. If a new role is needed, update `docs/public-ui-typography.md` first.

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

## Livewire Security Standard

The ecosystem standard remains Livewire 3. Do not upgrade package UI to Livewire 4 as part of security hardening.

Livewire components should treat all public properties, form data, and action parameters as untrusted input. Livewire checksum protection is useful framework protection, but package code should still re-authorize and re-resolve models before writing.

Use `#[Locked]` for server-owned state:

- route or model identity such as `uuid`, `post`, `page`, `gallery`, `section`
- mount-only configuration such as model class, model key, collection, context, redirect target, label options
- modal pending identifiers that are set by server methods and later confirmed
- read-only display state that should not be changed by the browser

Do not lock user-editable form state:

- text inputs
- selected options
- uploads
- search/filter values
- sort direction where the component validates an allowlist

Preferred component pattern:

```php
use Livewire\Attributes\Locked;

final class GalleryEdit extends Component
{
    #[Locked]
    public string $uuid;

    #[Locked]
    public Gallery $gallery;

    public GalleryForm $form;
}
```

Livewire components should:

- authorize direct page access in `mount()` or route middleware
- call Actions for writes
- let Actions authorize again before writing
- re-query models inside Actions or component methods using tenant-scoped queries
- validate allowlists for dynamic sort, filter, tab, status, and action names
- never trust hidden inputs or public properties for tenant, actor, role, paid access, or ownership
- keep secrets, tokens, payment provider state, and signed payloads out of public Livewire state
- validate uploads server-side before passing files to Actions

Direct page access still needs backend protection. Use route middleware, policies, `corexis_authorize()`, or equivalent package guards. Hiding buttons in Blade is only UX.

## UUID and Tenant Scope Standard

IvanBaric packages should use numeric database IDs as internal implementation details and UUIDs as public identifiers.

Use UUIDs for:

- route model binding
- Livewire action parameters
- Blade row keys where practical
- browser-facing edit/delete/reorder/attach/detach calls
- API payloads
- domain events that need a stable external identifier

Do not use browser-provided numeric IDs for meaningful state-changing operations when the model has a UUID. Numeric IDs may still exist as primary keys because Laravel, Spatie Media Library, pivots, and existing package schemas commonly depend on them.

Preferred migration shape:

```php
$table->id();
$table->uuid('uuid')->unique();
$table->foreignId('team_id')->nullable()->index();
```

Preferred model shape:

```php
public function getRouteKeyName(): string
{
    return 'uuid';
}
```

Actions and Livewire components should resolve browser-provided UUIDs through tenant-scoped queries:

```php
$page = Page::query()
    ->forTeam(corexis_tenant_id())
    ->where('uuid', $uuid)
    ->firstOrFail();
```

The ecosystem default tenant column remains `team_id`. In admin contexts, list queries and write lookups should both scope by the current Corexis tenant/team. A hidden button, locked public property, or UUID alone is not sufficient isolation.

Pivots and append-only log tables may use nullable UUID columns when they do not have a dedicated model lifecycle. Dedicated domain/event models should generate UUIDs on create, guarded with `Schema::hasColumn()` for backwards compatibility.

## Corexis Data Integrity Standard

Application code should validate intent, but the database must protect invariants that must never be duplicated.

Use database constraints for:

- public identifiers such as `uuid`
- tenant-scoped slugs such as `team_id + slug`
- unique machine keys such as permission codes, plan keys, language codes, and setting keys
- polymorphic attachment uniqueness such as `taxonomy_item_id + taxonomyable_type + taxonomyable_id`
- provider idempotency keys such as payment provider references and subscription identifiers
- one-owned-resource rules such as one gallery per owner and collection
- period usage rows such as owner, usage key, period start, and period end

Use Corexis idempotency for externally repeatable operations:

- payment redirects and payment confirmation
- webhook processing
- external API writes
- installer/sync steps that may be retried
- queue jobs that can be delivered more than once

Corexis provides:

- `corexis_idempotency()`
- `IvanBaric\Corexis\Support\IdempotencyStore`
- `corexis_idempotency_keys` migration

Preferred idempotency shape:

```php
return corexis_idempotency()->run(
    scope: 'billing',
    operation: 'payment_attempt.confirm',
    idempotencyKey: $request->header('Idempotency-Key'),
    callback: fn (): ActionResult => $action->handle(...),
);
```

Rules:

- The idempotency key must come from a stable external identifier when possible, such as a Stripe event id, provider payment id, webhook id, or client-generated UUID.
- Use a scoped unique key: `scope + operation + idempotency_key`.
- Store only safe JSON-serializable result summaries. Do not persist secrets, provider signatures, tokens, or whole model payloads as idempotency result data.
- Replayed requests should return the stored `ActionResult` and must not dispatch success domain events again.
- Use idempotency for retryable external workflows, not every normal admin form save.

Use transaction row locks for read-modify-write workflows where two requests must not update the same business state at the same time:

- billing payment confirmation
- subscription lifecycle changes
- plan usage recording and reset
- status transitions
- media reorder/delete/upload workflows
- content reorder and toggle workflows

Preferred migration examples:

```php
$table->uuid('uuid')->unique();
$table->unique(['team_id', 'slug']);
$table->unique(['provider', 'provider_reference']);
```

Preferred transaction locking example:

```php
DB::transaction(function () use ($id): void {
    $model = Model::query()
        ->whereKey($id)
        ->lockForUpdate()
        ->firstOrFail();

    $model->forceFill([...])->save();
});
```

Rules:

- PHP validation and Actions should check expected business failures before writes.
- Database constraints are still required for race conditions and duplicate requests.
- Use `lockForUpdate()` inside the transaction, not before it.
- Lock the row before calculating derived values such as toggles, counters, usage, expiry dates, and order positions.
- Do not use pessimistic locks for ordinary long-running admin forms. Use optimistic locking with `lock_version` for edit screens.
- Do not rely on Livewire, hidden inputs, disabled buttons, or frontend state for uniqueness.
- Keep constraints tenant-aware when a model belongs to a tenant.
- Name composite constraints when the generated name may be too long or needs to be stable.
- Prefer idempotency keys for payment/webhook/provider workflows.
- Append-only audit/event tables should have UUIDs and indexes, but only unique provider event keys when the source provides a stable event identifier.

Expected duplicate/conflict failures should be converted to `ActionResult::error(...)` in Actions where the package owns the write workflow. Unexpected constraint violations may still throw.

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

## Concurrency Standard

Editable admin models should use optimistic locking when concurrent edits can overwrite user work.

Preferred database column:

```php
$table->unsignedInteger('lock_version')->default(0);
```

Preferred model pattern:

```php
use IvanBaric\Corexis\Concerns\HasLockVersion;

class Page extends Model
{
    use HasLockVersion;
}
```

Preferred Action pattern:

```php
use IvanBaric\Corexis\Concerns\UsesOptimisticLocking;

final class UpdatePageAction
{
    use UsesOptimisticLocking;

    public function handle(Page $page, array $data): ActionResult
    {
        $expectedLockVersion = $this->pullExpectedLockVersion($data);

        $saved = DB::transaction(fn (): bool => $this->saveWithOptimisticLock(
            model: $page,
            attributes: $data,
            expectedLockVersion: $expectedLockVersion,
        ));

        if (! $saved) {
            return ActionResult::fromCorexis($this->staleModelResult());
        }
    }
}
```

Livewire forms should store the model's current `lock_version` when filling the form and send it back to the Action. If another user saves first, the Action returns `conflict.stale_model` and must not dispatch a success domain event.

For backwards compatibility, `lock_version` is optional. When no expected version is provided, Actions keep the existing save behavior. Existing host applications need an upgrade migration before strict conflict detection is active on already-installed tables.

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
