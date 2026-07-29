@props([
    'group',
])

@php
    /*
     * Resolve one stable Livewire state key for the complete option group.
     */
    $selectionKey = 'selections.'.$group->id;
@endphp

<fieldset
    data-menu-option-group
    data-required="{{ $group->is_required ? 'true' : 'false' }}"
    data-minimum="{{ $group->minimum_selections }}"
    data-maximum="{{ $group->maximum_selections }}"
    class="border-b border-primary/10 pb-7 last:border-b-0
        last:pb-0">
    <legend class="w-full">
        <span
            class="flex w-full flex-wrap items-center
                justify-between gap-3">
            <span
                class="font-display text-2xl text-primary-deep">
                {{ $group->name }}
            </span>

            <span
                class="rounded-full bg-surface-soft px-3 py-1
                    text-[0.62rem] font-semibold uppercase
                    tracking-[0.12em] text-primary">
                {{ $group->is_required ? 'Required' : 'Optional' }}
            </span>
        </span>
    </legend>

    <p class="mt-2 text-sm leading-6 text-muted">
        @if (
            $group->minimum_selections
            === $group->maximum_selections
        )
            Select {{ $group->minimum_selections }}.
        @else
            Select between {{ $group->minimum_selections }}
            and {{ $group->maximum_selections }}.
        @endif
    </p>

    @if ($group->options->isEmpty())
        <p
            class="mt-4 text-sm font-medium text-coral-deep"
            role="status">
            No options are currently available.
        </p>
    @elseif ($group->maximum_selections === 1)
        <div class="mt-4 grid gap-3">
            @if (
                ! $group->is_required
                && $group->minimum_selections === 0
            )
                <label
                    for="option-group-{{ $group->id }}-none"
                    class="menu-option-choice">
                    <span class="flex items-center gap-3">
                        <input
                            id="option-group-{{ $group->id }}-none"
                            type="radio"
                            name="option-group-{{ $group->id }}"
                            value=""
                            wire:model.live="{{ $selectionKey }}"
                            class="size-4 accent-primary">

                        <span class="font-medium text-ink">
                            No selection
                        </span>
                    </span>

                    <span class="text-sm text-muted">
                        Included
                    </span>
                </label>
            @endif

            @foreach ($group->options as $option)
                <label
                    for="menu-option-{{ $option->id }}"
                    class="menu-option-choice">
                    <span class="flex min-w-0 items-center gap-3">
                        <input
                            id="menu-option-{{ $option->id }}"
                            type="radio"
                            name="option-group-{{ $group->id }}"
                            value="{{ $option->id }}"
                            wire:model.live="{{ $selectionKey }}"
                            class="size-4 shrink-0 accent-primary">

                        <span class="min-w-0 font-medium text-ink">
                            {{ $option->name }}
                        </span>
                    </span>

                    <span
                        class="shrink-0 text-sm font-semibold
                            text-primary">
                        @if ($option->formattedAdditionalPrice())
                            +{{ $option->formattedAdditionalPrice() }}
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
                    for="menu-option-{{ $option->id }}"
                    class="menu-option-choice">
                    <span class="flex min-w-0 items-center gap-3">
                        <input
                            id="menu-option-{{ $option->id }}"
                            type="checkbox"
                            value="{{ $option->id }}"
                            wire:model.live="{{ $selectionKey }}"
                            class="size-4 shrink-0 accent-primary">

                        <span class="min-w-0 font-medium text-ink">
                            {{ $option->name }}
                        </span>
                    </span>

                    <span
                        class="shrink-0 text-sm font-semibold
                            text-primary">
                        @if ($option->formattedAdditionalPrice())
                            +{{ $option->formattedAdditionalPrice() }}
                        @else
                            Included
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
    @endif

    @error($selectionKey)
        <p
            class="mt-3 text-sm font-medium text-coral-deep"
            role="alert">
            {{ $message }}
        </p>
    @enderror
</fieldset>
