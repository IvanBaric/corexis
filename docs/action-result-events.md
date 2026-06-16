# ActionResult and Domain Events

Corexis provides the shared infrastructure primitives used by IvanBaric packages. It must stay model-agnostic and must not know about Velora, Pages, Gallery, SEO, Audit, Billing, application users, or application teams.

## Standard write flow

```text
Livewire Component -> Livewire Form Object -> Action -> ActionResult -> Domain Event -> Listener
```

## ActionResult

Use `IvanBaric\Corexis\Data\ActionResult` for expected operation outcomes that need to travel from package Actions to UI or API layers.

```php
use IvanBaric\Corexis\Data\ActionResult;

return ActionResult::success(
    message: __('Spremljeno.'),
    data: $model,
);

return ActionResult::error(
    message: __('Provjerite unesene podatke.'),
    code: 'validation_failed',
    errors: ['title' => [__('Naziv je obavezan.')]],
);
```

The DTO exposes:

- `success: bool`
- `message: string`
- `data: mixed`
- `code: string|null`
- `errors: array`

The existing positional API is kept for compatibility:

```php
ActionResult::error('Failed.', 'failed', ['legacy' => 'data']);
```

New code should prefer named arguments when returning structured errors.

## DomainEvent

Domain events may implement `IvanBaric\Corexis\Contracts\Events\DomainEvent` as a marker. The marker is intentionally empty so packages can opt in without coupling Corexis to their models.

Recommended event shape:

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
        public int|string $pageId,
    ) {}
}
```

Events should be final and readonly where safe, typed, small, serializable, and dispatched only after the write succeeds. Package listeners own cross-package side effects such as audit records, SEO refreshes, sitemap generation, media cleanup, cache invalidation, and billing access updates.

For the full package ecosystem standard, see `docs/ecosystem-architecture.md`.
