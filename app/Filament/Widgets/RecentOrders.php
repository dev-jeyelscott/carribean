<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrders extends TableWidget
{
    protected static ?int $sort = 20;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * Configure the latest order operations table.
     */
    public function table(
        Table $table,
    ): Table {
        return $table
            ->heading('Recent orders')
            ->query(
                Order::query()
                    ->latest('placed_at')
                    ->limit(8),
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order'),

                TextColumn::make('customer_name')
                    ->label('Customer'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn (OrderStatus $state): string => $state->label(),
                    ),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(
                        fn (PaymentStatus $state): string => $state->label(),
                    ),

                TextColumn::make('grand_total_cents')
                    ->label('Total')
                    ->formatStateUsing(
                        fn (int $state): string => Money::formatUsd(
                            $state,
                        ) ?? '$0.00',
                    ),

                TextColumn::make('placed_at')
                    ->label('Placed')
                    ->since(),
            ])
            ->recordUrl(
                fn (Order $record): string => OrderResource::getUrl(
                    'view',
                    [
                        'record' => $record,
                    ],
                ),
            )
            ->paginated(false);
    }
}
