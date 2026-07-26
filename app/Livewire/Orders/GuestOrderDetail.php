<?php

namespace App\Livewire\Orders;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout(
    'components.layouts.public',
    [
        'title' => 'Track Your Order',
        'description' => 'Review your Coast & Cay guest order and fulfillment progress.',
    ],
)]
final class GuestOrderDetail extends Component
{
    public Order $order;

    /**
     * Store the route-bound guest order after signed-route validation.
     */
    public function mount(Order $order): void
    {
        if ($order->user_id !== null) {
            abort(404);
        }

        $this->order = $order;
    }

    /**
     * Determine whether the guest may confirm receipt now.
     */
    #[Computed]
    public function canConfirmReceived(): bool
    {
        return in_array(
            $this->order->status,
            [
                OrderStatus::PickedUp,
                OrderStatus::Delivered,
            ],
            true,
        );
    }

    /**
     * Complete a fulfilled guest order from its secure tracking page.
     */
    public function confirmReceived(
        TransitionOrderStatus $transitionOrderStatus,
    ): void {
        if (! $this->canConfirmReceived()) {
            abort(403);
        }

        $this->order = $transitionOrderStatus->execute(
            order: $this->order,
            nextStatus: OrderStatus::Completed,
            publicNote: 'The guest customer confirmed that the order was received.',
        );

        unset($this->canConfirmReceived);

        session()->flash(
            'order_status',
            'Thanks. Your order is now marked complete.',
        );
    }

    /**
     * Render the guest-visible order snapshot and public history.
     */
    public function render(): View
    {
        $this->order->load([
            'items',
            'addresses',
            'statusHistories',
        ]);

        return view(
            'livewire.orders.guest-order-detail',
        );
    }
}
