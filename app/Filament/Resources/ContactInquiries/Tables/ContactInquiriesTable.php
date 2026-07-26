<?php

namespace App\Filament\Resources\ContactInquiries\Tables;

use App\Enums\ContactInquiryStatus;
use App\Models\ContactInquiry;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactInquiriesTable
{
    /**
     * Configure the contact inquiry workflow table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn (ContactInquiryStatus $state): string => $state->label(),
                    )
                    ->color(
                        fn (ContactInquiryStatus $state): string => $state->color(),
                    ),

                TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->visibleFrom('md'),

                TextColumn::make('phone')
                    ->searchable()
                    ->visibleFrom('lg'),

                TextColumn::make('subject')
                    ->searchable()
                    ->visibleFrom('lg'),

                TextColumn::make('is_read')
                    ->label('Review')
                    ->badge()
                    ->formatStateUsing(
                        fn (bool $state): string => $state
                            ? 'Reviewed'
                            : 'Unread',
                    )
                    ->color(
                        fn (bool $state): string => $state
                            ? 'gray'
                            : 'warning',
                    )
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(
                        ContactInquiryStatus::options(),
                    ),

                TernaryFilter::make('is_read')
                    ->label('Review status')
                    ->trueLabel('Reviewed')
                    ->falseLabel('Unread'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No contact inquiries yet')
            ->emptyStateDescription(
                'New contact inquiries will appear here for staff review.'
            )
            ->emptyStateIcon('heroicon-o-envelope')
            ->recordActions([
                ViewAction::make(),

                Action::make('startProgress')
                    ->label('Start Progress')
                    ->icon('heroicon-o-clock')
                    ->visible(
                        fn (ContactInquiry $record): bool => $record->status
                            === ContactInquiryStatus::New,
                    )
                    ->action(
                        fn (ContactInquiry $record): bool => $record->update([
                            'status' => ContactInquiryStatus::InProgress,
                            'is_read' => true,
                        ]),
                    ),

                Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->visible(
                        fn (ContactInquiry $record): bool => $record->status
                            !== ContactInquiryStatus::Resolved,
                    )
                    ->action(
                        fn (ContactInquiry $record): bool => $record->update([
                            'status' => ContactInquiryStatus::Resolved,
                            'is_read' => true,
                        ]),
                    ),

                Action::make('reopen')
                    ->label('Reopen')
                    ->visible(
                        fn (ContactInquiry $record): bool => $record->status
                            === ContactInquiryStatus::Resolved,
                    )
                    ->action(
                        fn (ContactInquiry $record): bool => $record->update([
                            'status' => ContactInquiryStatus::InProgress,
                        ]),
                    ),
            ]);
    }
}
