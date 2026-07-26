<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopSellingItems extends TableWidget
{
    protected static ?int $sort = 30;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * Configure a modest completed-order item leaderboard.
     */
    public function table(
        Table $table,
    ): Table {
        return $table
            ->heading('Top-selling items')
            ->description('Based on completed orders only.')
            ->query(
                OrderItem::query()
                    ->selectRaw(
                        '
                            MIN(order_items.id) AS id,
                            order_items.name,
                            SUM(order_items.quantity) AS total_quantity,
                            SUM(order_items.line_total_cents) AS total_revenue_cents
                        ',
                    )
                    ->whereHas(
                        'order',
                        fn (Builder $query): Builder => $query
                            ->where(
                                'status',
                                OrderStatus::Completed,
                            ),
                    )
                    ->groupBy('order_items.name')
                    ->orderByDesc('total_quantity')
                    ->limit(5),
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Menu item'),

                TextColumn::make('total_quantity')
                    ->label('Quantity sold'),

                TextColumn::make('total_revenue_cents')
                    ->label('Order value')
                    ->formatStateUsing(
                        fn (mixed $state): string => Money::formatUsd(
                            (int) $state,
                        ) ?? '$0.00',
                    ),
            ])
            ->paginated(false);
    }
}
