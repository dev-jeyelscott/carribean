<?php

namespace App\Filament\Resources\GalleryImages\Schemas;

use App\Rules\SafeImageDimensions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryImageForm
{
    /**
     * Configure the restaurant gallery-image editor.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns([
                        'default' => 1,
                        'xl' => 3,
                    ])
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Image details')
                                    ->description(
                                        'Use a descriptive title, category, and alt text so imagery is reusable and accessible.'
                                    )
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        TextInput::make('title')
                                            ->maxLength(180),

                                        Select::make('category')
                                            ->options([
                                                'dish' => 'Dish',
                                                'interior' => 'Interior',
                                                'ambiance' => 'Ambiance',
                                                'ingredients' => 'Ingredients',
                                                'team' => 'Team',
                                                'guests' => 'Guests',
                                                'about-hero' => 'About hero',
                                            ])
                                            ->native(false)
                                            ->searchable(),

                                        TextInput::make('alt_text')
                                            ->label('Alt text')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Display settings')
                                    ->description(
                                        'Control the gallery order and public visibility of this image.'
                                    )
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        TextInput::make('sort_order')
                                            ->label('Sort order')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),

                                        Toggle::make('is_visible')
                                            ->label('Visible publicly')
                                            ->default(true),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 2,
                            ]),

                        Section::make('Gallery image')
                            ->description(
                                'Upload a high-quality image that meets the restaurant image requirements.'
                            )
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Image')
                                    ->image()
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                    ])
                                    ->rules([
                                        new SafeImageDimensions,
                                    ])
                                    ->required()
                                    ->disk('public')
                                    ->directory('gallery')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->helperText(
                                        'JPEG, PNG, or WebP up to 2 MB. Responsive derivatives are generated automatically.'
                                    )
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 1,
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
