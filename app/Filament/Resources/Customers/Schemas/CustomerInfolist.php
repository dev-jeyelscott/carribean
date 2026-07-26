<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    /**
     * Configure read-only customer identity and account details.
     */
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema
            ->components([
                Section::make('Customer details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),

                        TextEntry::make('email')
                            ->copyable(),

                        TextEntry::make('phone')
                            ->copyable()
                            ->placeholder('Not provided'),

                        TextEntry::make('orders_count')
                            ->label('Total orders'),

                        TextEntry::make('email_verified_at')
                            ->label('Email verified')
                            ->dateTime()
                            ->placeholder('Not verified'),

                        TextEntry::make('created_at')
                            ->label('Registered')
                            ->dateTime(),
                    ]),
            ]);
    }
}
