<x-filament-widgets::widget>
    <section
        class="cc-dashboard-card"
        aria-labelledby="cc-recent-orders-heading"
    >
        <header class="cc-dashboard-card__header">
            <div>
                <p class="cc-dashboard-card__eyebrow">
                    Order management
                </p>

                <h2
                    id="cc-recent-orders-heading"
                    class="cc-dashboard-card__title"
                >
                    Recent orders
                </h2>
            </div>

            <span class="cc-dashboard-card__preview-label">
                Preview
            </span>
        </header>

        <div
            class="cc-dashboard-table-wrapper"
            tabindex="0"
            aria-label="Scrollable sample recent-orders table"
        >
            <table class="cc-dashboard-table">
                <caption class="sr-only">
                    Sample recent restaurant orders. This table is not
                    connected to operational data.
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Type</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col">Time</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order['number'] }}</strong>
                            </td>
                            <td>{{ $order['customer'] }}</td>
                            <td>{{ $order['fulfillment'] }}</td>
                            <td>{{ $order['total'] }}</td>
                            <td>
                                <span
                                    class="cc-dashboard-status
                                        cc-dashboard-status--{{ $order['status_tone'] }}"
                                >
                                    {{ $order['status'] }}
                                </span>
                            </td>
                            <td>{{ $order['time'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-filament-widgets::widget>
