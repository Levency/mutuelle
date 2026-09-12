@props(['title' => null, 'subtitle' => null])

<section {{ $attributes->merge(['class' => 'border border-rule bg-paper rounded-sm']) }}>
    @if($title)
        <header class="border-b border-rule px-5 py-4">
            <h2 class="font-display text-lg text-ink">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-0.5 text-sm text-ink-soft">{{ $subtitle }}</p>
            @endif
        </header>
    @endif
    <div>
        {{ $slot }}
    </div>
</section>
