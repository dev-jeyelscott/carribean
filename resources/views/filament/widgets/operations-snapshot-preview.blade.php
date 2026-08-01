<x-filament-widgets::widget>
    <section
        class="cc-dashboard-card"
        aria-labelledby="cc-operations-heading"
    >
        <header class="cc-dashboard-card__header">
            <div>
                <p class="cc-dashboard-card__eyebrow">
                    Daily service
                </p>

                <h2
                    id="cc-operations-heading"
                    class="cc-dashboard-card__title"
                >
                    Operations snapshot
                </h2>
            </div>

            <span class="cc-dashboard-card__preview-label">
                {{ $periodLabel }}
            </span>
        </header>

        <div class="cc-dashboard-insights">
            @foreach ($insights as $insight)
                <article class="cc-dashboard-insight">
                    <span
                        class="cc-dashboard-insight__icon-wrapper
                            cc-dashboard-insight__icon-wrapper--{{ $insight['tone'] }}"
                        aria-hidden="true"
                    >
                        <x-filament::icon
                            :icon="$insight['icon']"
                            class="cc-dashboard-insight__icon"
                        />
                    </span>

                    <div class="cc-dashboard-insight__content">
                        <strong>{{ $insight['label'] }}</strong>
                        <span>{{ $insight['detail'] }}</span>
                    </div>

                    <strong class="cc-dashboard-insight__value">
                        {{ $insight['value'] }}
                    </strong>
                </article>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
