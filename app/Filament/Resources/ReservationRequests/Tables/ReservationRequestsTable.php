<?php

namespace App\Filament\Resources\ReservationRequests\Tables;

use App\Enums\ReservationRequestStatus;
use App\Models\ReservationRequest;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReservationRequestsTable
{
    /**
     * Configure the manual reservation-request workflow table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn (ReservationRequestStatus $state): string => $state->label(),
                    )
                    ->color(
                        fn (ReservationRequestStatus $state): string => $state->color(),
                    ),

                TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->visibleFrom('md'),

                TextColumn::make('email')
                    ->searchable()
                    ->visibleFrom('lg'),

                TextColumn::make('preferred_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('preferred_time')
                    ->sortable()
                    ->visibleFrom('lg'),

                TextColumn::make('guest_count')
                    ->sortable()
                    ->visibleFrom('xl'),

                IconColumn::make('is_banquet_or_event')
                    ->boolean()
                    ->label('Event')
                    ->visibleFrom('xl'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(
                        ReservationRequestStatus::options(),
                    ),

                TernaryFilter::make('is_read')
                    ->label('Review status')
                    ->trueLabel('Reviewed')
                    ->falseLabel('Unread'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No reservation requests yet')
            ->emptyStateDescription(
                'New reservation requests will appear here for staff review.'
            )
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->recordActions([
                ViewAction::make(),

                Action::make('markContacted')
                    ->label('Mark Contacted')
                    ->icon('heroicon-o-phone')
                    ->visible(
                        fn (ReservationRequest $record): bool => $record->status
                            === ReservationRequestStatus::Pending,
                    )
                    ->action(
                        fn (ReservationRequest $record): bool => $record->update([
                            'status' => ReservationRequestStatus::Contacted,
                            'is_read' => true,
                        ]),
                    ),

                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->visible(
                        fn (ReservationRequest $record): bool => in_array(
                            $record->status,
                            [
                                ReservationRequestStatus::Pending,
                                ReservationRequestStatus::Contacted,
                            ],
                            true,
                        ),
                    )
                    ->action(
                        fn (ReservationRequest $record): bool => $record->update([
                            'status' => ReservationRequestStatus::Confirmed,
                            'is_read' => true,
                        ]),
                    ),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(
                        fn (ReservationRequest $record): bool => in_array(
                            $record->status,
                            [
                                ReservationRequestStatus::Pending,
                                ReservationRequestStatus::Contacted,
                            ],
                            true,
                        ),
                    )
                    ->action(
                        fn (ReservationRequest $record): bool => $record->update([
                            'status' => ReservationRequestStatus::Rejected,
                            'is_read' => true,
                        ]),
                    ),

                Action::make('cancel')
                    ->label('Cancel')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(
                        fn (ReservationRequest $record): bool => $record->status
                            === ReservationRequestStatus::Confirmed,
                    )
                    ->action(
                        fn (ReservationRequest $record): bool => $record->update([
                            'status' => ReservationRequestStatus::Cancelled,
                            'is_read' => true,
                        ]),
                    ),
            ]);
    }
}
