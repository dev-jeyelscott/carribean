<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Support\Money;
use App\Support\Rate;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CouponForm
{
    /**
     * Configure coupon identity, discount, limits, and availability.
     */
    public static function configure(
        Schema $schema,
    ): Schema {
        return $schema->components([
            Grid::make()
                ->columns([
                    'default' => 1,
                    'xl' => 3,
                ])
                ->schema([
                    Section::make('Coupon details')
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            TextInput::make('code')
                                ->required()
                                ->maxLength(64)
                                ->unique(ignoreRecord: true)
                                ->dehydrateStateUsing(
                                    fn (?string $state): string => Coupon::normalizeCode($state),
                                ),

                            Select::make('type')
                                ->options(
                                    CouponType::options(),
                                )
                                ->required()
                                ->default(
                                    CouponType::FixedAmount->value,
                                )
                                ->live()
                                ->afterStateUpdated(
                                    function (
                                        ?string $state,
                                        Set $set,
                                    ): void {
                                        if (
                                            $state
                                            === CouponType::FixedAmount->value
                                        ) {
                                            $set(
                                                'percentage_basis_points',
                                                null,
                                            );

                                            return;
                                        }

                                        $set(
                                            'fixed_discount_cents',
                                            null,
                                        );
                                    },
                                ),

                            TextInput::make(
                                'fixed_discount_cents',
                            )
                                ->label('Fixed discount')
                                ->prefix('$')
                                ->numeric()
                                ->inputMode('decimal')
                                ->step(0.01)
                                ->minValue(0.01)
                                ->visible(
                                    fn (Get $get): bool => $get('type')
                                        === CouponType::FixedAmount->value,
                                )
                                ->required(
                                    fn (Get $get): bool => $get('type')
                                        === CouponType::FixedAmount->value,
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
                                ),

                            TextInput::make(
                                'percentage_basis_points',
                            )
                                ->label('Percentage discount')
                                ->suffix('%')
                                ->numeric()
                                ->inputMode('decimal')
                                ->step(0.01)
                                ->minValue(0.01)
                                ->maxValue(100)
                                ->visible(
                                    fn (Get $get): bool => $get('type')
                                        === CouponType::Percentage->value,
                                )
                                ->required(
                                    fn (Get $get): bool => $get('type')
                                        === CouponType::Percentage->value,
                                )
                                ->formatStateUsing(
                                    fn (
                                        string|int|null $state,
                                    ): ?string => Rate::basisPointsToPercent(
                                        $state === null
                                            ? null
                                            : (int) $state,
                                    ),
                                )
                                ->dehydrateStateUsing(
                                    fn (
                                        string|int|float|null $state,
                                    ): ?int => Rate::percentToBasisPoints(
                                        $state,
                                    ),
                                ),

                            TextInput::make(
                                'minimum_subtotal_cents',
                            )
                                ->label('Minimum subtotal')
                                ->prefix('$')
                                ->numeric()
                                ->inputMode('decimal')
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
                                    ) ?? '0.00',
                                )
                                ->dehydrateStateUsing(
                                    fn (
                                        string|int|float|null $state,
                                    ): int => Money::decimalToCents(
                                        $state,
                                    ) ?? 0,
                                ),
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'xl' => 2,
                        ]),

                    Section::make('Availability')
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),

                            DateTimePicker::make('starts_at')
                                ->label('Starts at')
                                ->seconds(false),

                            DateTimePicker::make('expires_at')
                                ->label('Expires at')
                                ->seconds(false)
                                ->rules([
                                    'nullable',
                                    'date',
                                    'after_or_equal:starts_at',
                                ]),

                            TextInput::make('usage_limit')
                                ->label('Total usage limit')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->helperText(
                                    'Leave empty for no total limit.',
                                ),

                            TextInput::make('times_used')
                                ->label('Times used')
                                ->disabled()
                                ->dehydrated(false),
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
