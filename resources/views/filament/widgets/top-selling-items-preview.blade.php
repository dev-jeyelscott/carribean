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
                Preview
            </span>
        </header>

        <ol class="cc-dashboard-ranking">
            @foreach ($items as $item)
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
            @endforeach
        </ol>
    </section>
</x-filament-widgets::widget>
