@props([
    'icon' => 'photo',
    'iconClass' => 'cx-public-icon-xl',
])

@php
    $iconName = is_string($icon) && trim($icon) !== '' ? trim($icon) : 'photo';
    $iconClass = is_string($iconClass) && trim($iconClass) !== '' ? trim($iconClass) : 'cx-public-icon-xl';
@endphp

<div {{ $attributes->class('cx-public-media-placeholder') }}>
    <flux:icon :name="$iconName" class="{{ $iconClass }}" />
</div>
