<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    /**
     * Configure the public CMS page editor.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Page editor')
                    ->tabs([
                        Tab::make('Page details')
                            ->schema([
                                Section::make('Page content')
                                    ->description(
                                        'Create the page content guests will read on the restaurant website.'
                                    )
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(180),

                                        TextInput::make('slug')
                                            ->required()
                                            ->maxLength(160)
                                            ->unique(ignoreRecord: true)
                                            ->live(onBlur: true),

                                        Textarea::make('excerpt')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        RichEditor::make('content')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('About page storytelling')
                                    ->description(
                                        'These fields power the dedicated full-screen About page sections.'
                                    )
                                    ->visible(
                                        fn (Get $get): bool => $get('slug')
                                            === 'about',
                                    )
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        TextInput::make(
                                            'sections.hero.title',
                                        )
                                            ->label('Hero title')
                                            ->maxLength(180),

                                        TextInput::make(
                                            'sections.hero.accent',
                                        )
                                            ->label('Hero accent heading')
                                            ->maxLength(220),

                                        TextInput::make(
                                            'sections.story.eyebrow',
                                        )
                                            ->label('Story eyebrow')
                                            ->maxLength(100),

                                        TextInput::make(
                                            'sections.story.title',
                                        )
                                            ->label('Story title')
                                            ->maxLength(220),

                                        Textarea::make(
                                            'sections.story.description',
                                        )
                                            ->label('Story description')
                                            ->rows(5)
                                            ->columnSpanFull(),

                                        Textarea::make(
                                            'sections.story.quote',
                                        )
                                            ->label('Story quote')
                                            ->rows(2)
                                            ->maxLength(220)
                                            ->columnSpanFull(),

                                        TextInput::make(
                                            'sections.values_eyebrow',
                                        )
                                            ->label('Values eyebrow')
                                            ->maxLength(100),

                                        TextInput::make(
                                            'sections.values_title',
                                        )
                                            ->label('Values title')
                                            ->maxLength(220),

                                        Repeater::make('sections.values')
                                            ->label('Restaurant values')
                                            ->schema([
                                                TextInput::make('number')
                                                    ->maxLength(10),

                                                TextInput::make('title')
                                                    ->required()
                                                    ->maxLength(100),

                                                Textarea::make(
                                                    'description',
                                                )
                                                    ->required()
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->maxItems(4)
                                            ->defaultItems(4)
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->itemLabel(
                                                fn (array $state): ?string => is_string(
                                                    $state['title'] ?? null,
                                                )
                                                    ? $state['title']
                                                    : null,
                                            )
                                            ->columnSpanFull(),

                                        TextInput::make(
                                            'sections.heritage.eyebrow',
                                        )
                                            ->label('Heritage eyebrow')
                                            ->maxLength(100),

                                        TextInput::make(
                                            'sections.heritage.title',
                                        )
                                            ->label('Heritage title')
                                            ->maxLength(220),

                                        Textarea::make(
                                            'sections.heritage.description',
                                        )
                                            ->label('Heritage description')
                                            ->rows(4)
                                            ->columnSpanFull(),

                                        Repeater::make(
                                            'sections.heritage.items',
                                        )
                                            ->label('Heritage principles')
                                            ->schema([
                                                TextInput::make('title')
                                                    ->required()
                                                    ->maxLength(100),

                                                Textarea::make(
                                                    'description',
                                                )
                                                    ->required()
                                                    ->rows(3),
                                            ])
                                            ->maxItems(3)
                                            ->defaultItems(3)
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->itemLabel(
                                                fn (array $state): ?string => is_string(
                                                    $state['title'] ?? null,
                                                )
                                                    ? $state['title']
                                                    : null,
                                            )
                                            ->columnSpanFull(),

                                        TextInput::make(
                                            'sections.experience.eyebrow',
                                        )
                                            ->label('Experience eyebrow')
                                            ->maxLength(100),

                                        TextInput::make(
                                            'sections.experience.title',
                                        )
                                            ->label('Experience title')
                                            ->maxLength(220),

                                        Textarea::make(
                                            'sections.experience.description',
                                        )
                                            ->label('Experience description')
                                            ->rows(4)
                                            ->columnSpanFull(),

                                        Repeater::make(
                                            'sections.experience.items',
                                        )
                                            ->label('Guest experience points')
                                            ->schema([
                                                Textarea::make('text')
                                                    ->required()
                                                    ->rows(2),
                                            ])
                                            ->maxItems(4)
                                            ->defaultItems(4)
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->columnSpanFull(),

                                        TextInput::make(
                                            'sections.closing.eyebrow',
                                        )
                                            ->label('Closing eyebrow')
                                            ->maxLength(100),

                                        TextInput::make(
                                            'sections.closing.title',
                                        )
                                            ->label('Closing title')
                                            ->maxLength(220),

                                        Textarea::make(
                                            'sections.closing.description',
                                        )
                                            ->label('Closing description')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Publication')
                                    ->description(
                                        'Choose whether this page is visible on the public website.'
                                    )
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Published')
                                            ->default(true),
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('SEO details')
                            ->schema([
                                Section::make('Search preview')
                                    ->description(
                                        'Use concise metadata to help guests understand this page in search results.'
                                    )
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label('Meta title')
                                            ->maxLength(180)
                                            ->columnSpanFull(),

                                        Textarea::make(
                                            'meta_description',
                                        )
                                            ->label('Meta description')
                                            ->rows(4)
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
