<?php

namespace App\Console\Commands;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompleteFulfilledOrders extends Command
{
    protected $signature = 'orders:complete-fulfilled';

    protected $description = 'Complete orders that were picked up or delivered at least one hour ago.';

    /**
     * Complete eligible fulfilled orders in small database batches.
     */
    public function handle(
        TransitionOrderStatus $transitionOrderStatus,
    ): int {
        $cutoff = now()->subHour();

        $completed = 0;
        $skipped = 0;
        $failed = 0;

        Order::query()
            ->where(
                function (Builder $query) use ($cutoff): void {
                    $query
                        ->where(
                            function (Builder $pickupQuery) use ($cutoff): void {
                                $pickupQuery
                                    ->where(
                                        'status',
                                        OrderStatus::PickedUp,
                                    )
                                    ->whereNotNull('picked_up_at')
                                    ->where(
                                        'picked_up_at',
                                        '<=',
                                        $cutoff,
                                    );
                            },
                        )
                        ->orWhere(
                            function (Builder $deliveryQuery) use ($cutoff): void {
                                $deliveryQuery
                                    ->where(
                                        'status',
                                        OrderStatus::Delivered,
                                    )
                                    ->whereNotNull('delivered_at')
                                    ->where(
                                        'delivered_at',
                                        '<=',
                                        $cutoff,
                                    );
                            },
                        );
                },
            )
            ->orderBy('id')
            ->chunkById(
                100,
                function (
                    Collection $orders,
                ) use (
                    $transitionOrderStatus,
                    &$completed,
                    &$skipped,
                    &$failed,
                ): void {
                    /** @var Order $order */
                    foreach ($orders as $order) {
                        try {
                            $transitionOrderStatus->execute(
                                order: $order,
                                nextStatus: OrderStatus::Completed,
                                publicNote: 'This fulfilled order was completed automatically.',
                                internalNote: 'Automatically completed by the hourly scheduler.',
                            );

                            $completed++;
                        } catch (ValidationException) {
                            // Another process may already have moved the order.
                            $skipped++;
                        } catch (Throwable $exception) {
                            report($exception);

                            $this->error(
                                sprintf(
                                    'Order %s could not be completed: %s',
                                    $order->order_number
                                        ?? $order->public_id,
                                    $exception->getMessage(),
                                ),
                            );

                            $failed++;
                        }
                    }
                },
            );

        $this->info(
            sprintf(
                'Completed: %d; skipped: %d; failed: %d.',
                $completed,
                $skipped,
                $failed,
            ),
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
