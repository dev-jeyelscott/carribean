<?php

namespace App\View\Components\Public;

use App\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

final class BlogPreview extends Component
{
    /**
     * The latest public journal entries displayed by the component.
     *
     * @var Collection<int, BlogPost>
     */
    public Collection $posts;

    /**
     * Load the latest published journal entries.
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
     * Render the reusable homepage journal preview.
     */
    public function render(): View
    {
        return view('components.public.blog-preview');
    }
}
