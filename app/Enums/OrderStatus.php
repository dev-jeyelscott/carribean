<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingConfirmation = 'pending_confirmation';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case OutForDelivery = 'out_for_delivery';
    case PickedUp = 'picked_up';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * Return the customer-facing status label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingConfirmation => 'Pending Confirmation',
            self::Confirmed => 'Confirmed',
            self::Preparing => 'Preparing',
            self::ReadyForPickup => 'Ready for Pickup',
            self::OutForDelivery => 'Out for Delivery',
            self::PickedUp => 'Picked Up',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Return statuses that may legally follow this status.
     *
     * @return list<OrderStatus>
     */
    public function allowedNextStatuses(
        FulfillmentMethod $fulfillmentMethod,
    ): array {
        return match ($this) {
            self::PendingConfirmation => [
                self::Confirmed,
                self::Rejected,
                self::Cancelled,
            ],

            self::Confirmed => [
                self::Preparing,
                self::Cancelled,
            ],

            self::Preparing => match ($fulfillmentMethod) {
                FulfillmentMethod::Pickup => [
                    self::ReadyForPickup,
                ],

                FulfillmentMethod::Delivery => [
                    self::OutForDelivery,
                ],
            },

            self::ReadyForPickup => [
                self::PickedUp,
            ],

            self::OutForDelivery => [
                self::Delivered,
            ],

            self::PickedUp,
            self::Delivered => [
                self::Completed,
            ],

            self::Completed,
            self::Rejected,
            self::Cancelled => [],
        };
    }

    /**
     * Determine whether one requested transition is allowed.
     */
    public function canTransitionTo(
        OrderStatus $nextStatus,
        FulfillmentMethod $fulfillmentMethod,
    ): bool {
        return in_array(
            $nextStatus,
            $this->allowedNextStatuses(
                $fulfillmentMethod,
            ),
            true,
        );
    }

    /**
     * Determine whether no more status transitions are allowed.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed,
            self::Rejected,
            self::Cancelled => true,

            default => false,
        };
    }
}
