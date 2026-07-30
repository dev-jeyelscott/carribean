<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

final class AccountController extends Controller
{
    /**
     * Display the authenticated customer's account overview.
     */
    public function __invoke(): View
    {
        $user = $this->authenticatedUser();

        $orders = Order::query()
            ->where('user_id', $user->id);

        $orderCount = (clone $orders)->count();

        $activeOrderCount = (clone $orders)
            ->whereNotIn(
                'status',
                $this->terminalOrderStatusValues(),
            )
            ->count();

        $latestOrder = (clone $orders)
            ->withCount('items')
            ->latest('placed_at')
            ->latest('id')
            ->first();

        return view('pages.account.index', [
            'user' => $user,
            'orderCount' => $orderCount,
            'activeOrderCount' => $activeOrderCount,
            'latestOrder' => $latestOrder,
        ]);
    }

    /**
     * Return database values representing completed or closed orders.
     *
     * @return list<string>
     */
    private function terminalOrderStatusValues(): array
    {
        return [
            OrderStatus::Completed->value,
            OrderStatus::Rejected->value,
            OrderStatus::Cancelled->value,
        ];
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
