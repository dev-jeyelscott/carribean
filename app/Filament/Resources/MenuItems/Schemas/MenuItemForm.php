<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Rules\SafeImageDimensions;
use App\Support\Money;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MenuItemForm
{
    /**
     * Configure the menu-item form and its item-specific options.
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
                                Section::make('Menu item details')
                                    ->description(
                                        'Manage the public name, description, category, and authoritative base price.',
                                    )
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        Select::make('menu_category_id')
                                            ->label('Menu category')
                                            ->relationship(
                                                'menuCategory',
                                                'name',
                                            )
                                            ->required()
                                            ->searchable()
                                            ->preload(),

                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(180),

                                        TextInput::make('slug')
                                            ->required()
                                            ->maxLength(200)
                                            ->unique(ignoreRecord: true),

                                        TextInput::make('price_cents')
                                            ->label('Base price')
                                            ->prefix('$')
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->required(
                                                fn (Get $get): bool => (bool) $get(
                                                    'is_purchasable',
                                                ),
                                            )
                                            ->formatStateUsing(
                                                fn (
                                                    string|int|null $state,
                                                ): ?string => Money::centsToDecimal(
                                                    $state === null
                                                        ? null
                                                        : (int) $state,
                                                ),
                                            )
                                            ->dehydrateStateUsing(
                                                fn (
                                                    string|int|float|null $state,
                                                ): ?int => Money::decimalToCents(
                                                    $state,
                                                ),
                                            )
                                            ->helperText(
                                                'Stored internally in integer cents.',
                                            ),

                                        Textarea::make('description')
                                            ->rows(5)
                                            ->columnSpanFull(),

                                        TagsInput::make('dietary_labels')
                                            ->label('Dietary labels')
                                            ->placeholder(
                                                'Vegetarian, Vegan, Gluten-aware',
                                            )
                                            ->columnSpanFull(),

                                        Textarea::make(
                                            'allergen_information',
                                        )
                                            ->label('Allergen information')
                                            ->rows(3)
                                            ->helperText(
                                                'Use factual client-approved allergen information only.',
                                            )
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Ordering and display')
                                    ->description(
                                        'Visibility controls public display; availability and purchasability control ordering behavior.',
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
                                            ->minValue(0)
                                            ->required(),

                                        Toggle::make('is_visible')
                                            ->label('Visible publicly')
                                            ->default(true),

                                        Toggle::make('is_featured')
                                            ->label('Featured')
                                            ->default(false),

                                        Toggle::make('is_available')
                                            ->label('Currently available')
                                            ->default(true),

                                        Toggle::make('is_purchasable')
                                            ->label('Available for online ordering')
                                            ->default(true),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Options and add-ons')
                                    ->description(
                                        'Create item-specific groups such as Size, Spice Level, Side, or Extras.',
                                    )
                                    ->schema([
                                        Repeater::make('optionGroups')
                                            ->label('Option groups')
                                            ->relationship()
                                            ->orderColumn('sort_order')
                                            ->defaultItems(0)
                                            ->addActionLabel(
                                                'Add option group',
                                            )
                                            ->itemLabel(
                                                fn (array $state): ?string => $state['name'] ?? null,
                                            )
                                            ->collapsed()
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->maxLength(120),

                                                Toggle::make('is_required')
                                                    ->label(
                                                        'Customer must select',
                                                    )
                                                    ->default(false)
                                                    ->live(),

                                                TextInput::make(
                                                    'minimum_selections',
                                                )
                                                    ->label(
                                                        'Minimum selections',
                                                    )
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->required()
                                                    ->rules([
                                                        fn (
                                                            Get $get,
                                                        ): Closure => function (
                                                            string $attribute,
                                                            mixed $value,
                                                            Closure $fail,
                                                        ) use ($get): void {
                                                            if (
                                                                (bool) $get(
                                                                    'is_required',
                                                                )
                                                                && (int) $value
                                                                < 1
                                                            ) {
                                                                $fail(
                                                                    'A required group must require at least one selection.',
                                                                );
                                                            }
                                                        },
                                                    ]),

                                                TextInput::make(
                                                    'maximum_selections',
                                                )
                                                    ->label(
                                                        'Maximum selections',
                                                    )
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(0)
                                                    ->required()
                                                    ->rules([
                                                        fn (
                                                            Get $get,
                                                        ): Closure => function (
                                                            string $attribute,
                                                            mixed $value,
                                                            Closure $fail,
                                                        ) use ($get): void {
                                                            $minimum =
                                                                (int) (
                                                                    $get(
                                                                        'minimum_selections',
                                                                    )
                                                                    ?? 0
                                                                );

                                                            if (
                                                                (int) $value
                                                                < $minimum
                                                            ) {
                                                                $fail(
                                                                    'Maximum selections must be greater than or equal to minimum selections.',
                                                                );
                                                            }
                                                        },
                                                    ]),

                                                Repeater::make('options')
                                                    ->relationship()
                                                    ->orderColumn(
                                                        'sort_order',
                                                    )
                                                    ->minItems(1)
                                                    ->defaultItems(1)
                                                    ->addActionLabel(
                                                        'Add option',
                                                    )
                                                    ->itemLabel(
                                                        fn (
                                                            array $state,
                                                        ): ?string => $state['name']
                                                            ?? null,
                                                    )
                                                    ->schema([
                                                        TextInput::make(
                                                            'name',
                                                        )
                                                            ->required()
                                                            ->live(
                                                                onBlur: true,
                                                            )
                                                            ->maxLength(120),

                                                        TextInput::make(
                                                            'additional_price_cents',
                                                        )
                                                            ->label(
                                                                'Additional price',
                                                            )
                                                            ->prefix('$')
                                                            ->numeric()
                                                            ->inputMode(
                                                                'decimal',
                                                            )
                                                            ->step(0.01)
                                                            ->minValue(0)
                                                            ->default('0.00')
                                                            ->required()
                                                            ->formatStateUsing(
                                                                fn (
                                                                    string|int|null $state,
                                                                ): string => Money::centsToDecimal(
                                                                    $state === null
                                                                        ? 0
                                                                        : (int) $state,
                                                                )
                                                                    ?? '0.00',
                                                            )
                                                            ->dehydrateStateUsing(
                                                                fn (
                                                                    string|int|float|null $state,
                                                                ): int => Money::decimalToCents(
                                                                    $state,
                                                                )
                                                                    ?? 0,
                                                            ),

                                                        Toggle::make(
                                                            'is_available',
                                                        )
                                                            ->label(
                                                                'Available',
                                                            )
                                                            ->default(true),
                                                    ])
                                                    ->columns([
                                                        'default' => 1,
                                                        'md' => 3,
                                                    ])
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns([
                                                'default' => 1,
                                                'md' => 2,
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 2,
                            ]),

                        Section::make('Menu image')
                            ->description(
                                'Upload the primary menu image and provide meaningful alternative text.',
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
                                    ->disk('public')
                                    ->directory('menu-items')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->helperText(
                                        'JPEG, PNG, or WebP up to 2 MB. Responsive derivatives are generated automatically.',
                                    )
                                    ->columnSpanFull(),

                                TextInput::make('image_alt_text')
                                    ->label('Image alternative text')
                                    ->maxLength(255)
                                    ->helperText(
                                        'Describe the dish itself, not the filename.',
                                    ),
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
