<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Order Management';

    protected static ?string $navigationLabel = 'Orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?int $navigationSort = 10;

    /**
     * Configure the read-only order detail interface.
     */
    public static function infolist(
        Schema $schema,
    ): Schema {
        return OrderInfolist::configure(
            $schema,
        );
    }

    /**
     * Configure the operational order table.
     */
    public static function table(
        Table $table,
    ): Table {
        return OrdersTable::configure(
            $table,
        );
    }

    /**
     * Eager-load relationships required by the view page.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'items',
                'addresses',
                'payments',
                'statusHistories.changedByUser',
                'user',
            ]);
    }

    /**
     * Show pending-confirmation volume in the navigation.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = Order::query()
            ->where(
                'status',
                OrderStatus::PendingConfirmation,
            )
            ->count();

        return $count > 0
            ? (string) $count
            : null;
    }

    /**
     * Use a warning badge for orders requiring confirmation.
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    /**
     * Register the resource pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
