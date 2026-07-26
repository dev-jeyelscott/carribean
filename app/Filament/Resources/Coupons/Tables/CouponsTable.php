<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    /**
     * Configure coupon listing, filters, and edit actions.
     */
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(
                        fn (CouponType $state): string => $state->label(),
                    ),

                TextColumn::make('discount')
                    ->state(
                        fn (Coupon $record): string => $record->formattedDiscount(),
                    ),

                TextColumn::make(
                    'minimum_subtotal_cents',
                )
                    ->label('Minimum')
                    ->formatStateUsing(
                        fn (?int $state): string => Money::formatUsd($state)
                            ?? '$0.00',
                    )
                    ->visibleFrom('lg'),

                TextColumn::make('usage')
                    ->state(
                        fn (Coupon $record): string => $record->usage_limit === null
                            ? $record->times_used
                            .' / Unlimited'
                            : $record->times_used
                            .' / '
                            .$record->usage_limit,
                    )
                    ->visibleFrom('lg'),

                TextColumn::make('expires_at')
                    ->dateTime()
                    ->placeholder('No expiration')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(CouponType::options()),

                TernaryFilter::make('is_active')
                    ->label('Active status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->emptyStateHeading('No coupons yet')
            ->emptyStateDescription(
                'Create a simple fixed or percentage discount coupon.',
            )
            ->emptyStateIcon('heroicon-o-ticket')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
