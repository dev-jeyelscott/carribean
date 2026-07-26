<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class FaqForm
{
    /**
     * Configure the flat FAQ content and display controls.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Frequently asked question')
                    ->description(
                        'Keep each answer clear, accurate, and focused on one customer question.'
                    )
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('question')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        RichEditor::make('answer')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        Toggle::make('is_visible')
                            ->label('Visible publicly')
                            ->default(true),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
