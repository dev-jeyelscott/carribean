<?php

namespace App\Support\Dashboard;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class DashboardAnalytics
{
    /**
     * Return current and previous aggregate summaries.
     *
     * @return array{
     *     current: array{
     *         revenue_cents: int,
     *         orders_count: int,
     *         paid_orders_count: int,
     *         average_order_value_cents: int,
     *         pending_count: int,
     *         preparing_count: int
     *     },
     *     previous: array{
     *         revenue_cents: int,
     *         orders_count: int,
     *         paid_orders_count: int,
     *         average_order_value_cents: int,
     *         pending_count: int,
     *         preparing_count: int
     *     }
     * }
     */
    public function overview(
        DashboardPeriod $period,
    ): array {
        return [
            'current' => $this->summary($period),
            'previous' => $this->summary(
                $period->previous(),
            ),
        ];
    }

    /**
     * Return the primary order aggregates for one period.
     *
     * Revenue includes paid orders that have not been rejected or cancelled.
     *
     * @return array{
     *     revenue_cents: int,
     *     orders_count: int,
     *     paid_orders_count: int,
     *     average_order_value_cents: int,
     *     pending_count: int,
     *     preparing_count: int
     * }
     */
    public function summary(
        DashboardPeriod $period,
    ): array {
        $row = $this->ordersQuery($period)
            ->selectRaw(
                'COUNT(*) AS orders_count',
            )
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    SUM(
                        CASE
                            WHEN payment_status = ?
                                AND status NOT IN (?, ?)
                            THEN grand_total_cents
                            ELSE 0
                        END
                    ),
                    0
                ) AS revenue_cents
                SQL,
                [
                    PaymentStatus::Paid->value,
                    OrderStatus::Cancelled->value,
                    OrderStatus::Rejected->value,
                ],
            )
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    SUM(
                        CASE
                            WHEN payment_status = ?
                                AND status NOT IN (?, ?)
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS paid_orders_count
                SQL,
                [
                    PaymentStatus::Paid->value,
                    OrderStatus::Cancelled->value,
                    OrderStatus::Rejected->value,
                ],
            )
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    SUM(
                        CASE
                            WHEN status = ?
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS pending_count
                SQL,
                [
                    OrderStatus::PendingConfirmation->value,
                ],
            )
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    SUM(
                        CASE
                            WHEN status = ?
                            THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS preparing_count
                SQL,
                [
                    OrderStatus::Preparing->value,
                ],
            )
            ->first();

        $revenueCents = (int) (
            $row?->revenue_cents ?? 0
        );

        $paidOrdersCount = (int) (
            $row?->paid_orders_count ?? 0
        );

        return [
            'revenue_cents' => $revenueCents,
            'orders_count' => (int) (
                $row?->orders_count ?? 0
            ),
            'paid_orders_count' => $paidOrdersCount,
            'average_order_value_cents' => $paidOrdersCount > 0
                    ? (int) round(
                        $revenueCents
                            / $paidOrdersCount,
                    )
                    : 0,
            'pending_count' => (int) (
                $row?->pending_count ?? 0
            ),
            'preparing_count' => (int) (
                $row?->preparing_count ?? 0
            ),
        ];
    }

    /**
     * Return daily paid revenue for the selected and previous periods.
     *
     * @return array{
     *     labels: list<string>,
     *     current: list<int>,
     *     previous: list<int>
     * }
     */
    public function revenueSeries(
        DashboardPeriod $period,
    ): array {
        $previousPeriod = $period->previous();

        $currentByDate = $this->revenueByDay(
            $period,
        );

        $previousByDate = $this->revenueByDay(
            $previousPeriod,
        );

        $current = [];
        $previous = [];

        $currentKeys = $period->dateKeys();
        $previousKeys = $previousPeriod->dateKeys();

        foreach ($currentKeys as $index => $dateKey) {
            $current[] = $currentByDate[$dateKey] ?? 0;

            $previousDateKey =
                $previousKeys[$index] ?? null;

            $previous[] = $previousDateKey === null
                ? 0
                : ($previousByDate[$previousDateKey] ?? 0);
        }

        return [
            'labels' => $period->chartLabels(),
            'current' => $current,
            'previous' => $previous,
        ];
    }

    /**
     * Group detailed order statuses into the four dashboard categories.
     *
     * @return array{
     *     labels: list<string>,
     *     data: list<int>,
     *     completed: int,
     *     processing: int,
     *     pending: int,
     *     cancelled: int
     * }
     */
    public function statusCounts(
        DashboardPeriod $period,
    ): array {
        $counts = $this->ordersQuery($period)
            ->select('status')
            ->selectRaw(
                'COUNT(*) AS aggregate',
            )
            ->groupBy('status')
            ->pluck(
                'aggregate',
                'status',
            )
            ->map(
                static fn (mixed $value): int => (int) $value,
            )
            ->all();

        $count = static function (
            OrderStatus $status,
        ) use ($counts): int {
            return (int) (
                $counts[$status->value] ?? 0
            );
        };

        $completed = $count(
            OrderStatus::Completed,
        );

        $processing =
            $count(OrderStatus::Confirmed)
            + $count(OrderStatus::Preparing)
            + $count(OrderStatus::ReadyForPickup)
            + $count(OrderStatus::OutForDelivery)
            + $count(OrderStatus::PickedUp)
            + $count(OrderStatus::Delivered);

        $pending = $count(
            OrderStatus::PendingConfirmation,
        );

        $cancelled =
            $count(OrderStatus::Cancelled)
            + $count(OrderStatus::Rejected);

        return [
            'labels' => [
                'Completed',
                'Processing',
                'Pending',
                'Cancelled / rejected',
            ],
            'data' => [
                $completed,
                $processing,
                $pending,
                $cancelled,
            ],
            'completed' => $completed,
            'processing' => $processing,
            'pending' => $pending,
            'cancelled' => $cancelled,
        ];
    }

    /**
     * Return top-selling item snapshots from recognized paid orders.
     *
     * Quantity change is compared with the preceding equal-length period.
     *
     * @return list<array{
     *     name: string,
     *     quantity_sold: int,
     *     revenue_cents: int,
     *     change_percent: float|null
     * }>
     */
    public function topSellingItems(
        DashboardPeriod $period,
        int $limit = 5,
    ): array {
        $currentRows = $this->itemPerformance(
            $period,
            $limit,
        );

        $previousRows = $this->itemPerformance(
            $period->previous(),
            null,
        );

        $previousByItem = [];

        foreach ($previousRows as $row) {
            $previousByItem[
                $this->itemKey($row)
            ] = (int) $row->quantity_sold;
        }

        $items = [];

        foreach ($currentRows as $row) {
            $currentQuantity =
                (int) $row->quantity_sold;

            $previousQuantity = (int) (
                $previousByItem[
                    $this->itemKey($row)
                ] ?? 0
            );

            $items[] = [
                'name' => (string) $row->name,
                'quantity_sold' => $currentQuantity,
                'revenue_cents' => (int) (
                    $row->revenue_cents ?? 0
                ),
                'change_percent' => $this->percentageChange(
                    $currentQuantity,
                    $previousQuantity,
                ),
            ];
        }

        return $items;
    }

    /**
     * Return the most recently placed orders for the selected period.
     *
     * @return EloquentCollection<int, Order>
     */
    public function recentOrders(
        DashboardPeriod $period,
        int $limit = 5,
    ): EloquentCollection {
        $query = Order::query()
            ->whereBetween(
                'placed_at',
                [
                    $period->start,
                    $period->end,
                ],
            );

        if (
            $period->fulfillmentMethod
                instanceof FulfillmentMethod
        ) {
            $query->where(
                'fulfillment_method',
                $period->fulfillmentMethod->value,
            );
        }

        return $query
            ->orderByDesc('placed_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Count pickup and delivery orders for the selected period.
     *
     * @return array{
     *     pickup: int,
     *     delivery: int,
     *     total: int
     * }
     */
    public function fulfillmentCounts(
        DashboardPeriod $period,
    ): array {
        $counts = $this->ordersQuery($period)
            ->select('fulfillment_method')
            ->selectRaw(
                'COUNT(*) AS aggregate',
            )
            ->groupBy('fulfillment_method')
            ->pluck(
                'aggregate',
                'fulfillment_method',
            )
            ->map(
                static fn (mixed $value): int => (int) $value,
            )
            ->all();

        $pickup = (int) (
            $counts[
                FulfillmentMethod::Pickup->value
            ] ?? 0
        );

        $delivery = (int) (
            $counts[
                FulfillmentMethod::Delivery->value
            ] ?? 0
        );

        return [
            'pickup' => $pickup,
            'delivery' => $delivery,
            'total' => $pickup + $delivery,
        ];
    }

    /**
     * Start an order query constrained to the shared dashboard period.
     */
    private function ordersQuery(
        DashboardPeriod $period,
    ): Builder {
        $query = DB::table('orders')
            ->whereBetween(
                'placed_at',
                [
                    $period->start,
                    $period->end,
                ],
            );

        if (
            $period->fulfillmentMethod
                instanceof FulfillmentMethod
        ) {
            $query->where(
                'fulfillment_method',
                $period->fulfillmentMethod->value,
            );
        }

        return $query;
    }

    /**
     * Return recognized revenue indexed by calendar date.
     *
     * @return array<string, int>
     */
    private function revenueByDay(
        DashboardPeriod $period,
    ): array {
        $rows = $this->ordersQuery($period)
            ->where(
                'payment_status',
                PaymentStatus::Paid->value,
            )
            ->whereNotIn(
                'status',
                [
                    OrderStatus::Cancelled->value,
                    OrderStatus::Rejected->value,
                ],
            )
            ->selectRaw(
                'DATE(placed_at) AS date_key',
            )
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    SUM(grand_total_cents),
                    0
                ) AS revenue_cents
                SQL,
            )
            ->groupByRaw(
                'DATE(placed_at)',
            )
            ->orderBy('date_key')
            ->get();

        $data = [];

        foreach ($rows as $row) {
            $data[
                (string) $row->date_key
            ] = (int) $row->revenue_cents;
        }

        return $data;
    }

    /**
     * Aggregate item quantities and revenue for recognized paid orders.
     *
     * @return Collection<int, stdClass>
     */
    private function itemPerformance(
        DashboardPeriod $period,
        ?int $limit,
    ): Collection {
        $query = DB::table('order_items')
            ->join(
                'orders',
                'orders.id',
                '=',
                'order_items.order_id',
            )
            ->whereBetween(
                'orders.placed_at',
                [
                    $period->start,
                    $period->end,
                ],
            )
            ->where(
                'orders.payment_status',
                PaymentStatus::Paid->value,
            )
            ->whereNotIn(
                'orders.status',
                [
                    OrderStatus::Cancelled->value,
                    OrderStatus::Rejected->value,
                ],
            )
            ->select([
                'order_items.menu_item_id',
                'order_items.name',
            ])
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    SUM(order_items.quantity),
                    0
                ) AS quantity_sold
                SQL,
            )
            ->selectRaw(
                <<<'SQL'
                COALESCE(
                    SUM(order_items.line_total_cents),
                    0
                ) AS revenue_cents
                SQL,
            )
            ->groupBy([
                'order_items.menu_item_id',
                'order_items.name',
            ])
            ->orderByDesc('quantity_sold')
            ->orderByDesc('revenue_cents');

        if (
            $period->fulfillmentMethod
                instanceof FulfillmentMethod
        ) {
            $query->where(
                'orders.fulfillment_method',
                $period->fulfillmentMethod->value,
            );
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        /** @var Collection<int, stdClass> */
        return $query->get();
    }

    /**
     * Build a stable comparison key from an immutable item snapshot.
     */
    private function itemKey(
        stdClass $row,
    ): string {
        $identity = $row->menu_item_id === null
            ? 'deleted'
            : (string) $row->menu_item_id;

        return $identity.'|'.(string) $row->name;
    }

    /**
     * Calculate percentage change, returning null when no baseline exists.
     */
    private function percentageChange(
        int $current,
        int $previous,
    ): ?float {
        if ($previous === 0) {
            return $current > 0
                ? null
                : 0.0;
        }

        return round(
            (($current - $previous) / $previous)
                * 100,
            1,
        );
    }
}
