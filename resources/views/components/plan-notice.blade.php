@props([
    'heading' => __('Feature unavailable on this plan'),
    'message' => null,
    'actionHref' => null,
    'actionLabel' => __('Upgrade plan'),
    'actionIcon' => 'arrow-up-right',
    'icon' => 'lock-closed',
    'variant' => 'warning',
])

<flux:callout :icon="$icon" :variant="$variant" {{ $attributes }}>
    @if($heading)
        <flux:callout.heading>{{ $heading }}</flux:callout.heading>
    @endif

    @if($message)
        <flux:callout.text>{{ $message }}</flux:callout.text>
    @elseif($slot->isNotEmpty())
        <flux:callout.text>{{ $slot }}</flux:callout.text>
    @endif

    @if($actionHref)
        <x-slot name="actions">
            <flux:button size="sm" variant="primary" :icon="$actionIcon" :href="$actionHref" wire:navigate>
                {{ $actionLabel }}
            </flux:button>
        </x-slot>
    @endif
</flux:callout>
