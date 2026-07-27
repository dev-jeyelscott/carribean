<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\Orders\TransitionOrderStatus;
use App\Actions\Payments\MarkCashPaymentPaid;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Support\Orders\OrderNotificationDispatcher;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * Return lifecycle, payment, note, and notification actions.
     *
     * Business rules remain inside the existing application actions.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->transitionAction(
                status: OrderStatus::Confirmed,
                label: 'Confirm Order',
                icon: 'heroicon-o-check-circle',
                color: 'success',
            ),

            $this->transitionAction(
                status: OrderStatus::Rejected,
                label: 'Reject Order',
                icon: 'heroicon-o-x-circle',
                color: 'danger',
            ),

            $this->transitionAction(
                status: OrderStatus::Preparing,
                label: 'Mark Preparing',
                icon: 'heroicon-o-fire',
                color: 'primary',
            ),

            $this->transitionAction(
                status: OrderStatus::ReadyForPickup,
                label: 'Mark Ready for Pickup',
                icon: 'heroicon-o-shopping-bag',
                color: 'warning',
            ),

            $this->transitionAction(
                status: OrderStatus::OutForDelivery,
                label: 'Mark Out for Delivery',
                icon: 'heroicon-o-truck',
                color: 'warning',
            ),

            $this->transitionAction(
                status: OrderStatus::PickedUp,
                label: 'Mark Picked Up',
                icon: 'heroicon-o-hand-thumb-up',
                color: 'success',
            ),

            $this->transitionAction(
                status: OrderStatus::Delivered,
                label: 'Mark Delivered',
                icon: 'heroicon-o-home',
                color: 'success',
            ),

            $this->transitionAction(
                status: OrderStatus::Completed,
                label: 'Mark Completed',
                icon: 'heroicon-o-check-badge',
                color: 'success',
            ),

            $this->transitionAction(
                status: OrderStatus::Cancelled,
                label: 'Cancel Order',
                icon: 'heroicon-o-no-symbol',
                color: 'danger',
            ),

            Action::make('markCashPaymentPaid')
                ->label('Mark Cash Paid')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark cash payment as paid?')
                ->modalDescription(
                    'Use this only after staff has physically collected the cash payment.',
                )
                ->modalSubmitActionLabel('Mark Paid')
                ->visible(
                    fn (Order $record): bool => $record->payment_method->isCash()
                        && $record->payment_status === PaymentStatus::Pending
                        && ! in_array(
                            $record->status,
                            [
                                OrderStatus::Rejected,
                                OrderStatus::Cancelled,
                            ],
                            true,
                        ),
                )
                ->action(function (Order $record): void {
                    $updatedOrder = app(
                        MarkCashPaymentPaid::class,
                    )->execute($record);

                    $this->replaceRecord($updatedOrder);

                    Notification::make()
                        ->title('Cash payment marked as paid')
                        ->success()
                        ->send();
                }),

            Action::make('addInternalNote')
                ->label('Internal Note')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->fillForm(
                    fn (Order $record): array => [
                        'internal_note' => $record->internal_note,
                    ],
                )
                ->schema([
                    Textarea::make('internal_note')
                        ->label('Internal staff note')
                        ->helperText(
                            'This note is visible only inside the administration panel.',
                        )
                        ->rows(5)
                        ->maxLength(2_000),
                ])
                ->action(function (
                    array $data,
                    Order $record,
                ): void {
                    $note = trim(
                        (string) ($data['internal_note'] ?? ''),
                    );

                    $record->forceFill([
                        'internal_note' => $note === ''
                            ? null
                            : $note,
                    ])->save();

                    $this->replaceRecord(
                        $record->fresh([
                            'items',
                            'addresses',
                            'payments',
                            'statusHistories.changedByUser',
                            'user',
                        ]) ?? $record,
                    );

                    Notification::make()
                        ->title('Internal note updated')
                        ->success()
                        ->send();
                }),

            Action::make('resendCustomerNotification')
                ->label('Resend Customer Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Resend the current order update?')
                ->modalDescription(
                    'The customer will receive an email describing the order’s current status.',
                )
                ->modalSubmitActionLabel('Resend Email')
                ->action(function (Order $record): void {
                    $queued = app(
                        OrderNotificationDispatcher::class,
                    )->resendCurrentCustomerUpdate($record);

                    $notification = Notification::make();

                    if ($queued) {
                        $notification
                            ->title('Customer email queued')
                            ->success();
                    } else {
                        $notification
                            ->title('Customer email could not be queued')
                            ->warning();
                    }

                    $notification->send();
                }),
        ];
    }

    /**
     * Build one lifecycle transition action.
     */
    private function transitionAction(
        OrderStatus $status,
        string $label,
        string $icon,
        string $color,
    ): Action {
        return Action::make(
            'transitionTo'.$status->name,
        )
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->modalHeading($label.'?')
            ->modalDescription(
                sprintf(
                    'The order will move to “%s” and its public status history will be updated.',
                    $status->label(),
                ),
            )
            ->modalSubmitActionLabel($label)
            ->visible(
                fn (Order $record): bool => $record->status
                    ->canTransitionTo(
                        $status,
                        $record->fulfillment_method,
                    ),
            )
            ->action(function (Order $record) use ($status): void {
                $authenticatedUser = Auth::user();

                $updatedOrder = app(
                    TransitionOrderStatus::class,
                )->execute(
                    order: $record,
                    nextStatus: $status,
                    changedBy: $authenticatedUser instanceof User
                        ? $authenticatedUser
                        : null,
                );

                $this->replaceRecord($updatedOrder);

                Notification::make()
                    ->title(
                        sprintf(
                            'Order marked %s',
                            $status->label(),
                        ),
                    )
                    ->success()
                    ->send();
            });
    }

    /**
     * Replace the page record after an operation.
     */
    private function replaceRecord(Order $order): void
    {
        $this->record = $order;
    }
}
