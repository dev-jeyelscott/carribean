<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Rules\SafeImageDimensions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

final class BlogPostForm
{
    /**
     * Configure the Blog content, image, publication, and SEO fields.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Blog post editor')
                    ->tabs([
                        Tab::make('Content')
                            ->schema([
                                Section::make('Story')
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
                                            ->maxLength(180)
                                            ->unique(ignoreRecord: true),

                                        Textarea::make('excerpt')
                                            ->rows(4)
                                            ->maxLength(500)
                                            ->columnSpanFull(),

                                        RichEditor::make('body')
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Featured image')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Featured image')
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
                                            ->directory('blog-posts')
                                            ->visibility('public')
                                            ->maxSize(5120)
                                            ->imageEditor()
                                            ->preventFilePathTampering()
                                            ->helperText(
                                                'JPEG, PNG, or WebP up to 5 MB. Responsive derivatives are generated automatically.'
                                            ),

                                        TextInput::make('image_alt_text')
                                            ->label('Image alt text')
                                            ->maxLength(255)
                                            ->helperText(
                                                'Describe the image for guests using assistive technology.'
                                            ),
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Publication')
                            ->schema([
                                Section::make('Public visibility')
                                    ->description(
                                        'Draft posts stay in Filament and are excluded from navigation, search metadata, and the sitemap.'
                                    )
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Published')
                                            ->default(false),

                                        DateTimePicker::make('published_at')
                                            ->label('Published at')
                                            ->seconds(false)
                                            ->helperText(
                                                'Leave empty when publishing immediately; the application will set the current time.'
                                            ),
                                    ])
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('SEO')
                            ->schema([
                                Section::make('Search metadata')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label('Meta title')
                                            ->maxLength(180)
                                            ->columnSpanFull(),

                                        Textarea::make('meta_description')
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
