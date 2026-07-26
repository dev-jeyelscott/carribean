<?php

namespace App\Filament\Resources\MenuItems\Tables;

use App\Models\MenuItem;
use App\Services\ResponsiveImageManager;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MenuItemsTable
{
    /**
     * Configure the menu-item administration table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('responsive_thumbnail')
                    ->label('Image')
                    ->getStateUsing(
                        fn (MenuItem $record): ?string => $record->responsiveImagePath(
                            ResponsiveImageManager::VARIANT_THUMBNAIL,
                        ),
                    )
                    ->disk('public')
                    ->square()
                    ->visibleFrom('md'),

                TextColumn::make('menuCategory.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(
                        fn (?int $state): ?string => Money::formatUsd($state),
                    )
                    ->sortable()
                    ->visibleFrom('lg'),

                IconColumn::make('is_visible')
                    ->boolean()
                    ->label('Visible'),

                IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),

                IconColumn::make('is_purchasable')
                    ->boolean()
                    ->label('Orderable')
                    ->visibleFrom('lg'),

                IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured')
                    ->visibleFrom('xl'),

                TextColumn::make('sort_order')
                    ->sortable()
                    ->visibleFrom('xl'),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->trueLabel('Visible')
                    ->falseLabel('Hidden'),

                TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->trueLabel('Available')
                    ->falseLabel('Unavailable'),

                TernaryFilter::make('is_purchasable')
                    ->label('Online ordering')
                    ->trueLabel('Orderable')
                    ->falseLabel('Display only'),

                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->trueLabel('Featured')
                    ->falseLabel('Not featured'),
            ])
            ->emptyStateHeading('No menu items yet')
            ->emptyStateDescription(
                'Add a menu item to begin building the restaurant catalogue.',
            )
            ->emptyStateIcon('heroicon-o-book-open')
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
