<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Order Management';

    protected static ?string $navigationLabel = 'Customers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 20;

    /**
     * Configure the customer detail view.
     */
    public static function infolist(
        Schema $schema,
    ): Schema {
        return CustomerInfolist::configure(
            $schema,
        );
    }

    /**
     * Configure the customer table.
     */
    public static function table(
        Table $table,
    ): Table {
        return CustomersTable::configure(
            $table,
        );
    }

    /**
     * Load customer order counts while excluding the configured administrator.
     */
    public static function getEloquentQuery(): Builder
    {
        $adminEmail =
            config('admin.seed_user.email');

        return parent::getEloquentQuery()
            ->withCount('orders')
            ->when(
                is_string($adminEmail)
                    && $adminEmail !== '',
                fn (Builder $query): Builder => $query
                    ->whereRaw(
                        'LOWER(email) <> ?',
                        [
                            mb_strtolower($adminEmail),
                        ],
                    ),
            );
    }

    /**
     * Register only list and view pages.
     *
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
