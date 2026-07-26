<?php

namespace App\Livewire\Account;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout(
    'components.layouts.public',
    [
        'title' => 'Order Details',
        'description' => 'Review your Coast & Cay order and fulfillment progress.',
    ],
)]
final class OrderDetail extends Component
{
    public Order $order;

    /**
     * Resolve and authorize the customer-owned route-bound order.
     */
    public function mount(Order $order): void
    {
        Gate::authorize(
            'view',
            $order,
        );

        $this->order = $order;
    }

    /**
     * Determine whether the customer may confirm receipt now.
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
     * Complete a fulfilled order after confirmation from its owner.
     */
    public function confirmReceived(
        TransitionOrderStatus $transitionOrderStatus,
    ): void {
        $user = $this->authenticatedUser();

        Gate::authorize(
            'confirmReceived',
            $this->order,
        );

        $this->order = $transitionOrderStatus->execute(
            order: $this->order,
            nextStatus: OrderStatus::Completed,
            changedBy: $user,
            publicNote: 'The customer confirmed that the order was received.',
        );

        unset($this->canConfirmReceived);

        session()->flash(
            'order_status',
            'Thanks. Your order is now marked complete.',
        );
    }

    /**
     * Render the customer-visible order snapshot and public history.
     */
    public function render(): View
    {
        $this->order->load([
            'items',
            'addresses',
            'statusHistories',
        ]);

        return view(
            'livewire.account.order-detail',
        );
    }

    /**
     * Return the authenticated customer or deny access.
     */
    private function authenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
