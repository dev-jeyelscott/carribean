@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <p
        class="mb-3 text-xs font-semibold uppercase tracking-[0.28em]
            text-brand-coral-dark">
        Coast & Cay
    </p>

    <flux:heading
        size="xl"
        class="font-display text-3xl text-brand-palm-dark">
        {{ $title }}
    </flux:heading>

    <flux:subheading class="mt-2 leading-6 text-brand-muted">
        {{ $description }}
    </flux:subheading>
</div>
