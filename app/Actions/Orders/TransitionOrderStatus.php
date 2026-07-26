<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransitionOrderStatus
{
    /**
     * Lock an order and apply one valid lifecycle transition.
     */
    public function execute(
        Order $order,
        OrderStatus $nextStatus,
        ?User $changedBy = null,
        ?string $publicNote = null,
        ?string $internalNote = null,
    ): Order {
        return DB::transaction(
            function () use (
                $order,
                $nextStatus,
                $changedBy,
                $publicNote,
                $internalNote,
            ): Order {
                $lockedOrder = Order::query()
                    ->whereKey($order->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $previousStatus =
                    $lockedOrder->status;

                if (
                    ! $previousStatus
                        ->canTransitionTo(
                            $nextStatus,
                            $lockedOrder
                                ->fulfillment_method,
                        )
                ) {
                    throw ValidationException::withMessages([
                        'status' => sprintf(
                            'An order cannot move from %s to %s.',
                            $previousStatus->label(),
                            $nextStatus->label(),
                        ),
                    ]);
                }

                $lockedOrder->forceFill([
                    'status' => $nextStatus,
                    ...$this->timestampAttributes(
                        $nextStatus,
                    ),
                ])->save();

                $lockedOrder
                    ->statusHistories()
                    ->create([
                        'changed_by_user_id' => $changedBy?->id,

                        'previous_status' => $previousStatus,

                        'new_status' => $nextStatus,

                        'public_note' => $this->normalizeOptionalText(
                            $publicNote,
                        ),

                        'internal_note' => $this->normalizeOptionalText(
                            $internalNote,
                        ),
                    ]);

                return $lockedOrder->fresh([
                    'statusHistories',
                ]) ?? $lockedOrder;
            },
            3,
        );
    }

    /**
     * Return timestamp changes associated with milestone statuses.
     *
     * @return array<string, mixed>
     */
    private function timestampAttributes(
        OrderStatus $status,
    ): array {
        return match ($status) {
            OrderStatus::Rejected => [
                'rejected_at' => now(),
            ],

            OrderStatus::Cancelled => [
                'cancelled_at' => now(),
            ],

            OrderStatus::PickedUp => [
                'picked_up_at' => now(),
            ],

            OrderStatus::Delivered => [
                'delivered_at' => now(),
            ],

            OrderStatus::Completed => [
                'completed_at' => now(),
            ],

            default => [],
        };
    }

    /**
     * Trim optional notes and normalize blanks to null.
     */
    private function normalizeOptionalText(
        ?string $value,
    ): ?string {
        $normalized = trim(
            (string) $value,
        );

        return $normalized === ''
            ? null
            : $normalized;
    }
}
