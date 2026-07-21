@props([
    'icon' => 'inbox',
    'title' => null,
    'description' => null,
    'compact' => false,
])

@php
    $hasIcon = is_string($icon) && trim($icon) !== '';
@endphp

<div
    {{ $attributes->class([
        'cx-public-empty-state',
        'cx-public-empty-state-compact' => (bool) $compact,
        'text-center',
    ]) }}
>
    @if ($hasIcon)
        <div class="cx-public-empty-state-icon mx-auto">
            <flux:icon :name="$icon" class="cx-public-icon-lg" />
        </div>
    @endif

    @if ($title)
        <p class="cx-public-empty-state-title">{{ $title }}</p>
    @endif

    @if ($description)
        <p class="cx-public-empty-state-description">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mx-auto mt-4 max-w-xl">
            {{ $slot }}
        </div>
    @endif
</div>
