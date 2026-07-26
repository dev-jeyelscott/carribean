<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    /**
     * Configure the complete read-only operational order view.
     *
     * Each desktop column uses an independent vertical grid so taller sections
     * do not create empty spaces underneath sections in the opposite column.
     */
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 2,
                ])
                    ->dense()
                    ->schema([
                        /*
                         * First column:
                         * Customer, delivery address, and status history.
                         */
                        Grid::make(1)
                            ->dense()
                            ->schema([
                                Section::make('Customer')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('customer_name')
                                            ->label('Name'),

                                        TextEntry::make('customer_email')
                                            ->label('Email')
                                            ->copyable(),

                                        TextEntry::make('customer_phone')
                                            ->label('Phone')
                                            ->copyable(),

                                        TextEntry::make('customer_note')
                                            ->label('Customer note')
                                            ->placeholder('No customer note')
                                            ->columnSpanFull(),

                                        TextEntry::make('internal_note')
                                            ->label('Internal staff notes')
                                            ->placeholder('No internal notes')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Delivery address')
                                    ->schema([
                                        RepeatableEntry::make('addresses')
                                            ->hiddenLabel()
                                            ->columns(3)
                                            ->schema([
                                                TextEntry::make('recipient_name')
                                                    ->label('Recipient'),

                                                TextEntry::make('phone')
                                                    ->copyable(),

                                                TextEntry::make('postal_code')
                                                    ->label('ZIP code'),

                                                TextEntry::make('street_address')
                                                    ->label('Street address'),

                                                TextEntry::make('apartment_or_unit')
                                                    ->label('Unit')
                                                    ->placeholder('None'),

                                                TextEntry::make('city'),

                                                TextEntry::make('state'),

                                                TextEntry::make('delivery_instructions')
                                                    ->label('Delivery instructions')
                                                    ->placeholder('None')
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    ->visible(
                                        fn ($record): bool => $record->fulfillment_method
                                            === FulfillmentMethod::Delivery,
                                    ),

                                Section::make('Status history')
                                    ->schema([
                                        RepeatableEntry::make('statusHistories')
                                            ->hiddenLabel()
                                            ->columns(4)
                                            ->schema([
                                                TextEntry::make('previous_status')
                                                    ->label('From')
                                                    ->formatStateUsing(
                                                        fn (?OrderStatus $state): string => $state?->label()
                                                            ?? 'Order created',
                                                    ),

                                                TextEntry::make('new_status')
                                                    ->label('To')
                                                    ->formatStateUsing(
                                                        fn (OrderStatus $state): string => $state->label(),
                                                    ),

                                                TextEntry::make('changedByUser.name')
                                                    ->label('Changed by')
                                                    ->placeholder('System'),

                                                TextEntry::make('created_at')
                                                    ->label('Changed')
                                                    ->dateTime(),

                                                TextEntry::make('public_note')
                                                    ->label('Customer-facing note')
                                                    ->placeholder('None')
                                                    ->columnSpan(2),

                                                TextEntry::make('internal_note')
                                                    ->label('Internal note')
                                                    ->placeholder('None')
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ]),

                        /*
                         * Second column:
                         * Order summary, totals, items, and payments.
                         */
                        Grid::make(1)
                            ->dense()
                            ->schema([
                                Section::make('Order overview')
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('order_number')
                                            ->label('Order number')
                                            ->copyable(),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->formatStateUsing(
                                                fn (OrderStatus $state): string => $state->label(),
                                            ),

                                        TextEntry::make('payment_status')
                                            ->label('Payment')
                                            ->badge()
                                            ->formatStateUsing(
                                                fn (PaymentStatus $state): string => $state->label(),
                                            ),

                                        TextEntry::make('fulfillment_method')
                                            ->label('Fulfillment')
                                            ->formatStateUsing(
                                                fn (FulfillmentMethod $state): string => $state->label(),
                                            ),

                                        TextEntry::make('payment_method')
                                            ->label('Payment method')
                                            ->formatStateUsing(
                                                fn (PaymentMethod $state): string => $state->label(),
                                            ),

                                        TextEntry::make('placed_at')
                                            ->label('Placed')
                                            ->dateTime(),

                                        TextEntry::make('paid_at')
                                            ->label('Paid')
                                            ->dateTime()
                                            ->placeholder('Not paid'),

                                        TextEntry::make('completed_at')
                                            ->label('Completed')
                                            ->dateTime()
                                            ->placeholder('Not completed'),
                                    ]),

                                Section::make('Order totals')
                                    ->columns(5)
                                    ->schema([
                                        TextEntry::make('subtotal_cents')
                                            ->label('Subtotal')
                                            ->formatStateUsing(
                                                fn (int $state): string => Money::formatUsd(
                                                    $state,
                                                ) ?? '$0.00',
                                            ),

                                        TextEntry::make('discount_cents')
                                            ->label('Discount')
                                            ->formatStateUsing(
                                                fn (int $state): string => Money::formatUsd(
                                                    $state,
                                                ) ?? '$0.00',
                                            ),

                                        TextEntry::make('tax_cents')
                                            ->label('Tax')
                                            ->formatStateUsing(
                                                fn (int $state): string => Money::formatUsd(
                                                    $state,
                                                ) ?? '$0.00',
                                            ),

                                        TextEntry::make('delivery_cents')
                                            ->label('Delivery')
                                            ->formatStateUsing(
                                                fn (int $state): string => Money::formatUsd(
                                                    $state,
                                                ) ?? '$0.00',
                                            ),

                                        TextEntry::make('grand_total_cents')
                                            ->label('Grand total')
                                            ->weight('bold')
                                            ->formatStateUsing(
                                                fn (int $state): string => Money::formatUsd(
                                                    $state,
                                                ) ?? '$0.00',
                                            ),
                                    ]),

                                Section::make('Items')
                                    ->schema([
                                        RepeatableEntry::make('items')
                                            ->hiddenLabel()
                                            ->columns(4)
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Item'),

                                                TextEntry::make('quantity')
                                                    ->label('Quantity'),

                                                TextEntry::make('unit_price_cents')
                                                    ->label('Unit price')
                                                    ->formatStateUsing(
                                                        fn (int $state): string => Money::formatUsd(
                                                            $state,
                                                        ) ?? '$0.00',
                                                    ),

                                                TextEntry::make('line_total_cents')
                                                    ->label('Line total')
                                                    ->formatStateUsing(
                                                        fn (int $state): string => Money::formatUsd(
                                                            $state,
                                                        ) ?? '$0.00',
                                                    ),

                                                TextEntry::make('selected_options')
                                                    ->label('Selected options')
                                                    ->formatStateUsing(
                                                        fn (mixed $state): string => self::formatOptions(
                                                            $state,
                                                        ),
                                                    )
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),

                                Section::make('Payments')
                                    ->schema([
                                        RepeatableEntry::make('payments')
                                            ->hiddenLabel()
                                            ->columns(4)
                                            ->schema([
                                                TextEntry::make('payment_method')
                                                    ->label('Method')
                                                    ->formatStateUsing(
                                                        fn (PaymentMethod $state): string => $state->label(),
                                                    ),

                                                TextEntry::make('status')
                                                    ->badge()
                                                    ->formatStateUsing(
                                                        fn (PaymentStatus $state): string => $state->label(),
                                                    ),

                                                TextEntry::make('amount_cents')
                                                    ->label('Amount')
                                                    ->formatStateUsing(
                                                        fn (int $state): string => Money::formatUsd(
                                                            $state,
                                                        ) ?? '$0.00',
                                                    ),

                                                TextEntry::make('paid_at')
                                                    ->label('Paid')
                                                    ->dateTime()
                                                    ->placeholder('Not paid'),

                                                TextEntry::make('failure_message')
                                                    ->label('Failure')
                                                    ->placeholder('None')
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Format snapshotted selected options for staff review.
     */
    private static function formatOptions(
        mixed $state,
    ): string {
        if (! is_array($state) || $state === []) {
            return 'No options selected';
        }

        $labels = [];

        foreach ($state as $option) {
            if (! is_array($option)) {
                continue;
            }

            $groupName = trim(
                (string) (
                    $option['group_name']
                    ?? 'Option'
                ),
            );

            $name = trim(
                (string) (
                    $option['name']
                    ?? ''
                ),
            );

            if ($name !== '') {
                $labels[] = sprintf(
                    '%s: %s',
                    $groupName,
                    $name,
                );
            }
        }

        return $labels === []
            ? 'No options selected'
            : implode(', ', $labels);
    }
}
