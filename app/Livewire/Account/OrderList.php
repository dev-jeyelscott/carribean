<?php

namespace App\Livewire\Account;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout(
    'components.layouts.public',
    [
        'title' => 'My Orders',
        'description' => 'View your Coast & Cay order history and current order status.',
        'headerOverlay' => true,
    ],
)]
final class OrderList extends Component
{
    use WithPagination;

    /**
     * Authorize access to the authenticated customer order area.
     */
    public function mount(): void
    {
        Gate::authorize(
            'viewAny',
            Order::class,
        );
    }

    /**
     * Return the authenticated customer's paginated order history.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    #[Computed]
    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->where(
                'user_id',
                $this->authenticatedUser()->id,
            )
            ->withCount('items')
            ->latest('placed_at')
            ->latest('id')
            ->paginate(8);
    }

    /**
     * Render the customer order-history page.
     */
    public function render(): View
    {
        return view(
            'livewire.account.order-list',
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
