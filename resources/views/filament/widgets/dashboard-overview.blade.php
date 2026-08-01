<x-filament-widgets::widget>
    <section
        class="cc-dashboard-preview"
        aria-labelledby="cc-dashboard-preview-heading"
    >
        <div class="cc-dashboard-preview__heading">
            <div>
                <p
                    id="cc-dashboard-preview-heading"
                    class="cc-dashboard-preview__eyebrow"
                >
                    Restaurant performance
                </p>

                <p class="cc-dashboard-preview__copy">
                    Operational dashboard layout using temporary presentation
                    data.
                </p>
            </div>

            <span class="cc-dashboard-preview__badge" role="note">
                UI preview · Sample data
            </span>
        </div>

        <div class="cc-dashboard-kpi-grid">
            @foreach ($metrics as $metric)
                <article
                    class="cc-dashboard-kpi"
                    aria-label="{{ $metric['label'] }}:
                        {{ $metric['value'] }}.
                        {{ $metric['change'] }}."
                >
                    <span
                        class="cc-dashboard-kpi__icon-wrapper"
                        aria-hidden="true"
                    >
                        <x-filament::icon
                            :icon="$metric['icon']"
                            class="cc-dashboard-kpi__icon"
                        />
                    </span>

                    <div class="cc-dashboard-kpi__body">
                        <p class="cc-dashboard-kpi__label">
                            {{ $metric['label'] }}
                        </p>

                        <div class="cc-dashboard-kpi__value-row">
                            <strong class="cc-dashboard-kpi__value">
                                {{ $metric['value'] }}
                            </strong>

                            <span
                                class="cc-dashboard-kpi__change
                                    cc-dashboard-kpi__change--{{ $metric['change_tone'] }}"
                            >
                                {{ $metric['change'] }}
                            </span>
                        </div>

                        <p class="cc-dashboard-kpi__comparison">
                            {{ $metric['comparison'] }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
