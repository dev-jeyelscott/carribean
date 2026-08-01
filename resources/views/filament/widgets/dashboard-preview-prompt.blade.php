<x-filament-widgets::widget>
    <aside
        class="cc-dashboard-prompt"
        aria-labelledby="cc-dashboard-prompt-heading"
    >
        <span
            class="cc-dashboard-prompt__icon-wrapper"
            aria-hidden="true"
        >
            <x-filament::icon
                icon="heroicon-o-chart-bar-square"
                class="cc-dashboard-prompt__icon"
            />
        </span>

        <div class="cc-dashboard-prompt__content">
            <h2 id="cc-dashboard-prompt-heading">
                Analytics interface ready for integration
            </h2>

            <p>
                The layout, responsive behavior, chart components, states,
                and accessibility treatment are ready. Real order queries and
                shared filters remain intentionally disconnected.
            </p>
        </div>

        <button
            type="button"
            class="cc-dashboard-prompt__button"
            disabled
            aria-disabled="true"
            title="Data integration is not included in this UI-only phase"
        >
            Data wiring next
        </button>
    </aside>
</x-filament-widgets::widget>
