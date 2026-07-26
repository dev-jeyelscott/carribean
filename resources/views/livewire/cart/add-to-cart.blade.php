<div
    class="rounded-island border border-brand-palm/10 bg-white p-6
        shadow-island sm:p-8">
    @if ($item->is_available && $item->is_purchasable)
    <form wire:submit="addToCart">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="public-eyebrow">
                    Customize your order
                </p>

                <h2
                    class="mt-3 font-display text-3xl
                            text-brand-forest">
                    Choose your options
                </h2>
            </div>

            <p class="text-lg font-semibold text-brand-palm">
                {{ $item->formattedPrice() }}
            </p>
        </div>

        @error('cart')
        <x-public.alert type="error" class="mt-6">
            {{ $message }}
        </x-public.alert>
        @enderror

        @error('selections')
        <x-public.alert type="error" class="mt-6">
            {{ $message }}
        </x-public.alert>
        @enderror

        @if ($item->optionGroups->isNotEmpty())
        <div class="mt-8 space-y-7">
            @foreach ($item->optionGroups as $group)
            @php
            $selectionErrorKey =
            'selections.'.$group->id;
            @endphp

            <fieldset
                class="border-b border-brand-palm/10
                                pb-7 last:border-b-0 last:pb-0">
                <legend
                    class="flex w-full flex-wrap
                                    items-center justify-between gap-3">
                    <span
                        class="font-display text-2xl
                                        text-brand-forest">
                        {{ $group->name }}
                    </span>

                    <span
                        class="rounded-full bg-brand-cream
                                        px-3 py-1 text-xs font-semibold
                                        uppercase tracking-[0.12em]
                                        text-brand-palm">
                        {{ $group->is_required
                                        ? 'Required'
                                        : 'Optional' }}
                    </span>
                </legend>

                <p class="mt-2 text-sm text-brand-muted">
                    @if (
                    $group->minimum_selections
                    === $group->maximum_selections
                    )
                    Select
                    {{ $group->minimum_selections }}.
                    @else
                    Select between
                    {{ $group->minimum_selections }}
                    and
                    {{ $group->maximum_selections }}.
                    @endif
                </p>

                @if ($group->options->isEmpty())
                <p
                    class="mt-4 text-sm font-medium
                                        text-brand-coral-dark">
                    No options are currently available.
                </p>
                @elseif ($group->maximum_selections === 1)
                <div class="mt-4 grid gap-3">
                    @if (
                    ! $group->is_required
                    && $group->minimum_selections === 0
                    )
                    <label
                        class="flex min-h-12
                                                cursor-pointer items-center
                                                gap-3 rounded-2xl border
                                                border-brand-palm/10
                                                px-4 py-3 transition
                                                hover:border-brand-palm/30">
                        <input
                            type="radio"
                            name="option-group-{{ $group->id }}"
                            value=""
                            wire:model="selections.{{ $group->id }}"
                            class="size-4">

                        <span
                            class="font-medium
                                                    text-brand-forest">
                            No selection
                        </span>
                    </label>
                    @endif

                    @foreach ($group->options as $option)
                    <label
                        for="option-{{ $option->id }}"
                        class="flex min-h-12
                                                cursor-pointer items-center
                                                justify-between gap-4
                                                rounded-2xl border
                                                border-brand-palm/10
                                                px-4 py-3 transition
                                                hover:border-brand-palm/30">
                        <span
                            class="flex items-center
                                                    gap-3">
                            <input
                                id="option-{{ $option->id }}"
                                type="radio"
                                name="option-group-{{ $group->id }}"
                                value="{{ $option->id }}"
                                wire:model="selections.{{ $group->id }}"
                                class="size-4">

                            <span
                                class="font-medium
                                                        text-brand-forest">
                                {{ $option->name }}
                            </span>
                        </span>

                        <span
                            class="text-sm font-semibold
                                                    text-brand-palm">
                            @if (
                            $option
                            ->formattedAdditionalPrice()
                            )
                            +
                            {{ $option
                                                        ->formattedAdditionalPrice() }}
                            @else
                            Included
                            @endif
                        </span>
                    </label>
                    @endforeach
                </div>
                @else
                <div class="mt-4 grid gap-3">
                    @foreach ($group->options as $option)
                    <label
                        for="option-{{ $option->id }}"
                        class="flex min-h-12
                                                cursor-pointer items-center
                                                justify-between gap-4
                                                rounded-2xl border
                                                border-brand-palm/10
                                                px-4 py-3 transition
                                                hover:border-brand-palm/30">
                        <span
                            class="flex items-center
                                                    gap-3">
                            <input
                                id="option-{{ $option->id }}"
                                type="checkbox"
                                value="{{ $option->id }}"
                                wire:model="selections.{{ $group->id }}"
                                class="size-4">

                            <span
                                class="font-medium
                                                        text-brand-forest">
                                {{ $option->name }}
                            </span>
                        </span>

                        <span
                            class="text-sm font-semibold
                                                    text-brand-palm">
                            @if (
                            $option
                            ->formattedAdditionalPrice()
                            )
                            +
                            {{ $option
                                                        ->formattedAdditionalPrice() }}
                            @else
                            Included
                            @endif
                        </span>
                    </label>
                    @endforeach
                </div>
                @endif

                @error($selectionErrorKey)
                <p
                    class="mt-3 text-sm font-medium
                                        text-brand-coral-dark"
                    role="alert">
                    {{ $message }}
                </p>
                @enderror
            </fieldset>
            @endforeach
        </div>
        @endif

        <div
            class="mt-8 flex flex-col gap-5 border-t
                    border-brand-palm/10 pt-7 sm:flex-row
                    sm:items-end">
            <div class="w-full sm:max-w-32">
                <label
                    for="cart-quantity"
                    class="text-sm font-semibold text-brand-forest">
                    Quantity
                </label>

                <input
                    id="cart-quantity"
                    type="number"
                    min="1"
                    max="{{ \App\Support\Cart\SessionCart::MAX_QUANTITY }}"
                    step="1"
                    wire:model.number="quantity"
                    class="mt-2 min-h-12 w-full rounded-full
                            border border-brand-palm/20 bg-brand-cream
                            px-5 text-brand-forest">

                @error('quantity')
                <p
                    class="mt-2 text-sm font-medium
                                text-brand-coral-dark"
                    role="alert">
                    {{ $message }}
                </p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="addToCart"
                class="public-button-primary w-full
                        disabled:cursor-not-allowed disabled:opacity-60
                        sm:flex-1">
                <span wire:loading.remove wire:target="addToCart">
                    Add to Cart
                </span>

                <span wire:loading wire:target="addToCart">
                    Adding…
                </span>
            </button>
        </div>
    </form>
    @elseif (! $item->is_available)
    <x-public.alert type="warning">
        This item is currently unavailable.
    </x-public.alert>
    @else
    <x-public.alert type="warning">
        This menu item is not available for online ordering.
    </x-public.alert>
    @endif
</div>