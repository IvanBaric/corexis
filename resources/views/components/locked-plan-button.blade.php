@props([
    'label' => null,
    'plan' => 'Pro',
    'tooltip' => null,
    'icon' => 'lock-closed',
    'type' => 'button',
])

@if($tooltip)
    <flux:tooltip :content="$tooltip">
        <div class="inline-flex">
            <flux:button :type="$type" :icon="$icon" disabled {{ $attributes }}>
                <span class="inline-flex items-center gap-2">
                    <span>{{ $label ?? $slot }}</span>
                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 ring-1 ring-zinc-950/10 dark:bg-zinc-900 dark:text-zinc-400 dark:ring-white/10">{{ $plan }}</span>
                </span>
            </flux:button>
        </div>
    </flux:tooltip>
@else
    <flux:button :type="$type" :icon="$icon" disabled {{ $attributes }}>
        <span class="inline-flex items-center gap-2">
            <span>{{ $label ?? $slot }}</span>
            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500 ring-1 ring-zinc-950/10 dark:bg-zinc-900 dark:text-zinc-400 dark:ring-white/10">{{ $plan }}</span>
        </span>
    </flux:button>
@endif
