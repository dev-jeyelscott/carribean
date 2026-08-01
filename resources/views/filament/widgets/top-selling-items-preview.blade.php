<x-filament-widgets::widget>
    <section
        class="cc-dashboard-card"
        aria-labelledby="cc-top-selling-heading"
    >
        <header class="cc-dashboard-card__header">
            <div>
                <p class="cc-dashboard-card__eyebrow">
                    Menu performance
                </p>

                <h2
                    id="cc-top-selling-heading"
                    class="cc-dashboard-card__title"
                >
                    Top-selling items
                </h2>
            </div>

            <span class="cc-dashboard-card__preview-label">
                {{ $periodLabel }}
            </span>
        </header>

        <ol class="cc-dashboard-ranking">
            @forelse ($items as $item)
                <li class="cc-dashboard-ranking__item">
                    <span
                        class="cc-dashboard-ranking__rank"
                        aria-hidden="true"
                    >
                        {{ $item['rank'] }}
                    </span>

                    <div class="cc-dashboard-ranking__identity">
                        <strong>{{ $item['name'] }}</strong>
                        <span>{{ $item['orders'] }}</span>
                    </div>

                    <div class="cc-dashboard-ranking__value">
                        <strong>{{ $item['revenue'] }}</strong>
                        <span>{{ $item['trend'] }}</span>
                    </div>
                </li>
            @empty
                <li class="cc-dashboard-ranking__item">
                    <div class="cc-dashboard-ranking__identity">
                        <strong>No paid item sales</strong>
                        <span>
                            No recognized item revenue exists for this period.
                        </span>
                    </div>
                </li>
            @endforelse
        </ol>
    </section>
</x-filament-widgets::widget>
