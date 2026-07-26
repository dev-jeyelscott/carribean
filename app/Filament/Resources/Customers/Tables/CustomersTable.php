<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    /**
     * Configure the searchable customer directory.
     */
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->placeholder('Not provided'),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->placeholder('Not verified'),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort(
                'created_at',
                'desc',
            )
            ->emptyStateHeading('No registered customers yet')
            ->emptyStateDescription('Registered restaurant customers will appear here.')
            ->emptyStateIcon('heroicon-o-users')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
