@props([
    'title',
    'description',
    'eyebrow' => 'Customer account',
])

<div class="auth-header">
    <p class="auth-header__kicker">
        {{ $eyebrow }}
    </p>

    <flux:heading
        size="xl"
        class="font-display !text-3xl !leading-tight
            !text-brand-palm-dark sm:!text-4xl">
        {{ $title }}
    </flux:heading>

    <flux:subheading
        class="mx-auto mt-3 max-w-md !text-sm !leading-7
            !text-brand-muted">
        {{ $description }}
    </flux:subheading>
</div>
