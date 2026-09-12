@props(['tone' => 'neutral'])

@php
    $toneClasses = match ($tone) {
        'success' => 'border-brand text-brand-dark bg-brand/[0.06]',
        'warning' => 'border-gold text-gold bg-gold/[0.08]',
        'danger'  => 'border-stamp text-stamp bg-stamp/[0.06]',
        default   => 'border-ink-soft text-ink-soft bg-ink-soft/[0.06]',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-block -rotate-2 rounded-[3px] border-2 px-2 py-0.5 font-mono text-[11px] font-semibold uppercase tracking-wider {$toneClasses}"]) }}>
    {{ $slot }}
</span>
