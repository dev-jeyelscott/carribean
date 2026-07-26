<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Allow authenticated customers to enter their order area.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Allow customers to view their own orders and administrators to view all.
     */
    public function view(
        User $user,
        Order $order,
    ): bool {
        return $order->user_id === $user->id
            || $user->isAdministrator();
    }

    /**
     * Allow registered customers to place their own orders.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Allow the owning customer to complete a fulfilled order.
     */
    public function confirmReceived(
        User $user,
        Order $order,
    ): bool {
        return $order->user_id === $user->id
            && in_array(
                $order->status,
                [
                    OrderStatus::PickedUp,
                    OrderStatus::Delivered,
                ],
                true,
            );
    }

    /**
     * Reserve general order updates for the configured administrator.
     */
    public function update(
        User $user,
        Order $order,
    ): bool {
        return $user->isAdministrator();
    }

    /**
     * Prevent order deletion because orders are historical financial records.
     */
    public function delete(
        User $user,
        Order $order,
    ): bool {
        return false;
    }

    /**
     * Prevent destructive mass deletion.
     */
    public function deleteAny(
        User $user,
    ): bool {
        return false;
    }
}
