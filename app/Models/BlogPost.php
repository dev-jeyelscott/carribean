<?php

namespace App\Models;

use App\Models\Concerns\HasResponsiveImages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $body
 * @property string|null $image_path
 * @property string|null $image_alt_text
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $image_url
 */
#[Fillable([
    'title',
    'slug',
    'excerpt',
    'body',
    'image_path',
    'image_alt_text',
    'is_published',
    'published_at',
    'meta_title',
    'meta_description',
])]
class BlogPost extends Model
{
    use HasResponsiveImages;

    /**
     * Set a stable publication timestamp when a draft is published.
     */
    protected static function booted(): void
    {
        static::saving(function (BlogPost $blogPost): void {
            if (
                $blogPost->is_published
                && $blogPost->published_at === null
            ) {
                $blogPost->published_at = now();
            }
        });
    }

    /**
     * Return the model casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Resolve public Blog routes through their readable slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Limit a query to Blog posts currently visible to guests.
     *
     * @param  Builder<BlogPost>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query
            ->where('is_published', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere(
                        'published_at',
                        '<=',
                        now(),
                    );
            });
    }

    /**
     * Order Blog posts by their public publication timestamp.
     *
     * @param  Builder<BlogPost>  $query
     */
    #[Scope]
    protected function latestPublished(Builder $query): void
    {
        $query
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * Determine whether this Blog post is publicly available.
     */
    public function isPubliclyVisible(): bool
    {
        return $this->is_published
            && (
                $this->published_at === null
                || $this->published_at->lessThanOrEqualTo(now())
            );
    }
}
