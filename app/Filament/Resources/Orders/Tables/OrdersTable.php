<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    /**
     * Configure searchable order operations and filters.
     */
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn (OrderStatus $state): string => $state->label(),
                    )
                    ->color(
                        fn (OrderStatus $state): string => self::orderStatusColor(
                            $state,
                        ),
                    ),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(
                        fn (PaymentStatus $state): string => $state->label(),
                    )
                    ->color(
                        fn (PaymentStatus $state): string => self::paymentStatusColor(
                            $state,
                        ),
                    ),

                TextColumn::make('fulfillment_method')
                    ->label('Fulfillment')
                    ->formatStateUsing(
                        fn (FulfillmentMethod $state): string => $state->label(),
                    ),

                TextColumn::make('grand_total_cents')
                    ->label('Total')
                    ->formatStateUsing(
                        fn (int $state): string => Money::formatUsd(
                            $state,
                        ) ?? '$0.00',
                    )
                    ->sortable(),

                TextColumn::make('placed_at')
                    ->label('Placed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(
                        self::orderStatusOptions(),
                    ),

                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options(
                        self::paymentStatusOptions(),
                    ),

                SelectFilter::make('fulfillment_method')
                    ->label('Fulfillment')
                    ->options(
                        FulfillmentMethod::options(),
                    ),
            ])
            ->defaultSort(
                'placed_at',
                'desc',
            )
            ->emptyStateHeading('No customer orders yet')
            ->emptyStateDescription('Placed customer orders will appear here.')
            ->emptyStateIcon('heroicon-o-receipt-percent')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    /**
     * Return order-status filter options.
     *
     * @return array<string, string>
     */
    private static function orderStatusOptions(): array
    {
        $options = [];

        foreach (OrderStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    /**
     * Return payment-status filter options.
     *
     * @return array<string, string>
     */
    private static function paymentStatusOptions(): array
    {
        $options = [];

        foreach (PaymentStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    /**
     * Return the Filament badge color for an order status.
     */
    private static function orderStatusColor(
        OrderStatus $status,
    ): string {
        return match ($status) {
            OrderStatus::PendingConfirmation => 'warning',
            OrderStatus::Confirmed => 'info',
            OrderStatus::Preparing => 'primary',
            OrderStatus::ReadyForPickup,
            OrderStatus::OutForDelivery => 'warning',
            OrderStatus::PickedUp,
            OrderStatus::Delivered,
            OrderStatus::Completed => 'success',
            OrderStatus::Rejected,
            OrderStatus::Cancelled => 'danger',
        };
    }

    /**
     * Return the Filament badge color for a payment status.
     */
    private static function paymentStatusColor(
        PaymentStatus $status,
    ): string {
        return match ($status) {
            PaymentStatus::Pending => 'warning',
            PaymentStatus::Paid => 'success',
            PaymentStatus::Failed => 'danger',
            PaymentStatus::Refunded => 'gray',
        };
    }
}
