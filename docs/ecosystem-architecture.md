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

## Deployment Configuration Standard

Production-sensitive deployment choices must stay in configuration or shared Corexis helpers, not inside package views, Livewire components, form objects, or actions.

Do not hardcode disks, drivers, buckets, external URLs, providers, AI models, queue names, or similar environment-dependent options in reusable package code. For public uploaded media, use `corexis_public_media_disk()` when storing files and `corexis_public_media_url()` when rendering stored paths.

Fallbacks are allowed in config files or Corexis helpers, but they should not be duplicated across package code because the host app must be able to switch from local public storage to S3 or another configured disk without code changes.

## Public UI Typography Standard

Reusable public website layouts should follow the Corexis public Tailwind typography roles documented in `docs/public-ui-typography.md`.

The standard rule is: choose text size by semantic role, not by individual layout variation. Section titles, section descriptions, item titles, item descriptions, meta text, lead text, CTA text, and featured titles should keep consistent Tailwind classes across comparable layout variants.

Do not introduce a new text size in a package layout unless it represents a new documented text role. If a new role is needed, update `docs/public-ui-typography.md` first.

## Public UI Media Standard

Reusable public website media should follow `docs/public-ui-media.md`. That standard covers image frames, radius, hover, overlays, aspect ratios, placeholders, upload validation, media-library conversion usage, responsive images, and when public layouts may use original uploaded files.

The standard rule is: public layouts should render the smallest suitable named conversion for the role, and use the original upload only for lightbox/fullscreen views or as a final fallback when a conversion is not available.

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

Every Livewire component change must include a security pass before completion. Review public properties, action parameters, model resolution, authorization, tenant scope, and destructive/privileged modal flows. Do not treat this as an optional hardening step after the feature is done.

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
- put the Livewire update route behind a central rate limiter such as `throttle:livewire-updates`, with the per-minute limit coming from host app configuration
- keep Livewire temporary upload rules and throttling centralized in `config/livewire.php`, with image size limits derived from `config('corexis.image_uploads.default.max_file_size_kb')`

For destructive or privileged modal flows, prefer this pattern:

- `confirm...($uuid)` receives browser input, resolves the model through a tenant-scoped query, validates authorization, and sets a `#[Locked]` pending UUID/name
- `delete`, `archive`, `restore`, `publish`, `detach`, or equivalent final action receives no direct UUID unless there is a specific reason
- the final action re-resolves the pending UUID and authorizes again before writing

Stateful Flux modals must be opened only after the server has prepared their state. Avoid `<flux:modal.trigger>` around a `wire:click` for edit/confirm modals, because the browser opens the modal before Livewire finishes replacing the previous entity data. The Livewire method should reset old state, resolve and authorize the entity, fill the form or pending UUID, then call `Flux::modal(...)->show()` or dispatch `modal-show`. The modal must reset its state on close (`x-on:close="$wire.cancel...()"`, `@cancel`, or equivalent) so closing by X, ESC, backdrop, or cancel does not leak stale state into the next opening.

Direct page access still needs backend protection. Use route middleware, policies, `corexis_authorize()`, or equivalent package guards. Hiding buttons in Blade is only UX.

Custom route middleware that protects a Livewire page must be reviewed for persistent middleware support. Middleware without arguments should be registered in the host app through `Livewire::addPersistentMiddleware(...)` so it is re-applied to later Livewire update requests. Middleware with arguments is not supported by Livewire persistent middleware definitions, so role/permission middleware such as `permission:*` or `role:*` must be backed by authorization inside the component, Action, policy, or domain service before any sensitive write.

## Auth And Invitation Security Standard

Authentication and invitation flows must always have explicit server-side rate limiting. Fortify login limits should key attempts by normalized username/email and IP address, two-factor challenges by the pending login session, and passkey attempts by credential or session plus IP address.

Public invitation links must use signed URLs, short expiry, and tokens stored only as hashes. Invitation routes should have route-level IP throttling, and preview/submit handling should also have token-specific throttling so both random-token scans and repeated attacks against a real token are limited.

Accepting an invitation must re-check the invitation inside a `lockForUpdate()` transaction before assigning membership or roles. Accepted, revoked, and expired invitations must not be reusable, and the role attached to the invitation must still be assignable immediately before acceptance.

## Livewire CSP Standard

Livewire 4 CSP-safe mode should be supported as a production hardening option, but it must not be enabled blindly. Host projects should expose it through `config('livewire.csp_safe')`, defaulting to disabled until a browser compatibility pass is complete.

When writing Livewire and Alpine UI:

- avoid complex inline expressions that are hard to run under strict CSP: arrow functions, template literals, spread syntax, dynamic method/property access, and large JavaScript blocks in attributes
- move complex Alpine behavior into named methods/getters in the `x-data` object or into registered `Alpine.data()` modules
- keep Livewire expressions simple method calls, property bindings, or validated parameter calls
- avoid new inline `<script>` blocks unless the host project has a nonce or bundled asset strategy

Before enabling strict CSP without `'unsafe-eval'`:

- set `config('livewire.csp_safe')` to `true` on local or staging first
- test critical admin and public flows in a real browser
- check the console for CSP, Alpine, and Livewire expression errors
- verify file uploads, modals, dropdowns, pagination, lazy sections, and any custom Alpine widgets

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

Shared admin layout and responsiveness belong in `admin-ui`, not in each host application. Keep repeated Flux modal sizing, mobile gutters, editor overflow fixes, cursor behavior, panel/page spacing, and required-field marker styling in `packages/ivanbaric/admin-ui/resources/css/admin-ui.css`.

The shared admin baseline is deliberately restrained: one visual boundary per content group, 8px ordinary surface radius, no hover movement on static cards, adaptive stat grids, common list thumbnails/empty states, and pagination only when a paginator has more than one page. Profile, security, package settings, superadmin, and tenant screens must use the same presentational primitives instead of retaining framework starter UI. Business behavior remains in the host/domain package; only the repeatable presentation belongs in `admin-ui`.

When a configured editor has content/design/settings tabs, the section definition is the source of truth for both the tab and the page header. The active tab label and description should update the main header so the current task is explicit. Do not duplicate those labels in host views or leave a static entity title above every tab.

Admin list and table screens must render a visible list header whenever they render rows. Use the shared `admin-list-header` class directly before the row container and keep its grid columns aligned with `admin-list-row`. This applies to pages, page sections, posts, products, taxonomies, users, audit logs, and future admin lists. Empty states can omit the header, but row-based screens must not ship without column labels.

For standard admin screens, `.admin-page` is the only owner of the page gutter and `[data-flux-main]` is the only vertical scroll owner. Flux main ships with its own padding, so `admin-ui` must remove that padding when `.admin-page` is its direct child. Never stack both paddings: the duplicated vertical space creates artificial overflow and an unnecessary scrollbar on otherwise short pages.

Required admin fields should not use the HTML `required` attribute when the project expects Livewire/backend validation to be shown first. Use backend validation rules, add `data-required` to the Flux control, and let `admin-ui.css` render the red visual marker on the generated Flux label. This avoids browser-native validation messages taking over before Livewire validation. Use `x-admin-ui::required-label` only for custom field markup that cannot use Flux's generated label.

Mobile admin screens should avoid nested bordered cards. Admin panels should not draw an outer ring/shadow or add extra horizontal padding on small screens; the page gutter should control width. Upload controls, helper panels, and form blocks inside an existing admin panel should use shared admin-ui surface classes so small screens keep one calm visual boundary instead of multiple stacked borders.

Mobile admin forms should have more vertical breathing room than desktop forms. Common form stack spacing belongs in `admin-ui.css`, not in one-off `mt-*`, `space-y-*`, or `gap-*` fixes scattered through host application views.

Admin dropdowns should use Flux `variant="listbox"` by default. This includes settings dropdowns, icon pickers, filters, and ordinary form selects. A different select variant should be an intentional local design decision, not an accidental default.

Primary Livewire submit actions should use `x-admin-ui::submit-button` from the shared admin-ui package. The button must use a scoped `wire:target`, disable itself during the Livewire request, keep the Flux loading indicator, and switch its label to `Spremanje...` while saving. Large admin forms should keep the primary save button inside the same `<form wire:submit="...">` as the fields being saved, including when the button is visually placed in the page header. Do not rely on external Flux submit buttons or the HTML `form` attribute for primary save flows unless there is a focused test proving the action fires. Large admin forms should also use `wire:loading.class="admin-panel-content-loading"` plus `x-admin-ui::loading-overlay` with the same `Spremanje...` text. Reusable projects must not ship save buttons that can be submitted repeatedly during the same Livewire request.

When an external form target is genuinely required, reusable Blade wrappers must forward the original `$attributes` bag directly into the nested Flux component. Renamed attribute bags and inline Blade conditionals inside nested component attributes can be compiled as literal bogus attributes. Verify the rendered button contains the expected `form` and `wire:target` attributes, not only that the source Blade looks correct.

Large edit forms with autosave should follow the same dirty-state pattern used by posts: locked `savedStateSnapshot`, `isDirty()` comparison, `Nema promjena za spremanje.` info toast for clean manual saves, `wire:poll.180000ms="autoSave"` for background saves, `Automatsko spremanje podataka` only after an actual autosave, and a visible last-saved timestamp with the user who made the last edit. Autosave must not make the primary save button look like it is loading.

Focused onboarding/setup flows should use a reusable admin-ui layout pattern rather than a normal admin page shell. The flow should be short, one topic per step, visually stable between steps, and free of unrelated navigation. When the next step depends on required input, the button should be disabled until the step is complete and switch from neutral to primary when ready. This is UX only; backend validation remains mandatory.

Local SQLite development should not use database-backed cache or sessions. Use `CACHE_STORE=file` and `SESSION_DRIVER=file` or Redis. SQLite allows only limited concurrent writes, so database cache/session writes from Livewire updates, rate limiters, and session persistence can intermittently throw `SQLSTATE[HY000]: database is locked`. Production should use Redis or a production database/cache setup instead of SQLite.

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

## Initial Content Setup Standard

Apps that generate starter website content should expose one focused setup route, preferably short and memorable, such as `/app/ai`. Avoid burying the flow under implementation-oriented paths like `/app/website/ai-content`.

The initial setup middleware should guard all admin routes until the required setup is complete, with an explicit allowlist for the setup, processing, status, completion, logout, and safe support routes. If the app uses Livewire pages behind this middleware, register the middleware as Livewire persistent middleware so subsequent Livewire requests keep the same protection.

Local development must have a configuration-based bypass for paid AI generation and long setup flows. The bypass belongs in config, not in temporary route edits or commented middleware.

The app should deterministically create the structural shell: pages, sections, required records, ordering, and default visibility. AI should adapt text and content inside that shell based on user answers. Do not let the model invent arbitrary application structure, routes, permissions, records, or media relationships.

Copy in this flow should be outcome-first and brief. Avoid bureaucratic labels such as "anketa" when a softer term like "kratki odabir" or "prilagodba" fits better. State the value in one clear sentence: the app will create pages, sections, and starter content that users can edit later. Duration should be shown as a small badge, not repeated across paragraphs.

Break the setup into relaxed steps with one topic each. Typical steps are: introduction, work areas, values, participants, activities, highlights, tone of voice, and review. Keep each screen understandable in a few seconds.

Required choices in setup flows should use live Livewire bindings so button state updates immediately. Use `wire:model.live` for toggles/selects and a small debounce for text fallback fields. Disabled buttons are not validation; run server-side validation before generation.

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
],
```

Host applications may use team language in their resolver class names because the app owns the concrete tenant model. Packages should keep using Corexis tenant language and must not call app-specific `team()`, `current_team_id()`, or package-local tenant helpers directly.

Package-local tenant/team resolvers should be migrated to Corexis and removed. The host application owns the single concrete tenant resolver used by every package.

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

## Transactional Email Standard

Host applications should keep transactional email branding in one configuration namespace instead of hardcoding a separate color and font in every mail view. Branded emails should use the host application's name, primary color, font stack, and optional hosted web-font stylesheet, with system font fallbacks.

Production email markup should be table-based with essential styles inline. Primary actions should remain usable in Outlook and restrictive webmail clients, and security-sensitive messages such as invitations should include the relevant role/context, expiration time, and a plain fallback URL below the main call to action.

Email visuals should match the application while staying deliberately simpler than the web UI. The message must remain readable when web fonts, media queries, shadows, and rounded corners are ignored.

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
