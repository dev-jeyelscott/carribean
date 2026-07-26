<?php

namespace App\View\Components\Public;

use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

final class BlogPreview extends Component
{
    /**
     * @var Collection<int, BlogPost>
     */
    public Collection $posts;

    /**
     * Load a maximum of three current Blog posts for the homepage.
     */
    public function __construct()
    {
        $this->posts = BlogPost::query()
            ->published()
            ->latestPublished()
            ->limit(3)
            ->get();
    }

    /**
     * Avoid rendering an empty homepage section.
     */
    public function shouldRender(): bool
    {
        return $this->posts->isNotEmpty();
    }

    /**
     * Render the conditional homepage Blog preview.
     */
    public function render(): View
    {
        return view(
            'components.public.blog-preview',
        );
    }
}
