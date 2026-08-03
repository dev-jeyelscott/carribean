<?php

namespace App\Livewire\PublicSite;

use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

final class BlogIndex extends Component
{
    private const INITIAL_POST_COUNT = 7;

    private const LOAD_MORE_COUNT = 6;

    public int $visiblePosts = self::INITIAL_POST_COUNT;

    /**
     * Increase the number of visible posts by one grid-sized batch.
     */
    public function loadMore(): void
    {
        $publishedPostCount = BlogPost::query()
            ->published()
            ->count();

        $this->visiblePosts = min(
            $this->visiblePosts + self::LOAD_MORE_COUNT,
            $publishedPostCount,
        );
    }

    /**
     * Render the published journal and determine whether another batch exists.
     */
    public function render(): View
    {
        /** @var Collection<int, BlogPost> $posts */
        $posts = BlogPost::query()
            ->published()
            ->latestPublished()
            ->limit($this->visiblePosts + 1)
            ->get();

        $hasMore = $posts->count() > $this->visiblePosts;

        return view(
            'livewire.public-site.blog-index',
            [
                'posts' => $posts
                    ->take($this->visiblePosts)
                    ->values(),
                'hasMore' => $hasMore,
            ],
        );
    }
}
