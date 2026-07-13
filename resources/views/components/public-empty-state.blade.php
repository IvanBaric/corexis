@props([
    'icon' => 'inbox',
    'title' => null,
    'description' => null,
    'align' => 'center',
    'compact' => false,
])

@php
    $alignment = in_array($align, ['start', 'center'], true) ? $align : 'center';
    $hasIcon = is_string($icon) && trim($icon) !== '';
@endphp

<div
    {{ $attributes->class([
        'cx-public-empty-state',
        'cx-public-empty-state-compact' => (bool) $compact,
        'text-left' => $alignment === 'start',
        'text-center' => $alignment === 'center',
    ]) }}
>
    @if ($hasIcon)
        <div @class([
            'cx-public-empty-state-icon',
            'mx-auto' => $alignment === 'center',
        ])>
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
        <div @class([
            'mt-4',
            'mx-auto max-w-xl' => $alignment === 'center',
        ])>
            {{ $slot }}
        </div>
    @endif
</div>
