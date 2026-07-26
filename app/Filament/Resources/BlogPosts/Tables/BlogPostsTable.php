<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Models\BlogPost;
use App\Services\ResponsiveImageManager;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class BlogPostsTable
{
    /**
     * Configure the Blog-post listing and publication filter.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('responsive_thumbnail')
                    ->label('Image')
                    ->getStateUsing(
                        fn (BlogPost $record): ?string => $record
                            ->responsiveImagePath(
                                ResponsiveImageManager::VARIANT_THUMBNAIL,
                            ),
                    )
                    ->disk('public')
                    ->square()
                    ->visibleFrom('md'),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->visibleFrom('lg'),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Draft'),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true,
                    ),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Publication')
                    ->trueLabel('Published')
                    ->falseLabel('Draft'),
            ])
            ->defaultSort(
                'published_at',
                'desc',
            )
            ->emptyStateHeading('No Blog posts yet')
            ->emptyStateDescription(
                'Create a draft story and publish it when the content is approved.'
            )
            ->emptyStateIcon('heroicon-o-newspaper')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
