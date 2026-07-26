<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

final class BlogController extends Controller
{
    /**
     * Display published Blog posts using a simple paginated journal.
     */
    public function index(): View
    {
        return view('pages.blog.index', [
            'posts' => BlogPost::query()
                ->published()
                ->latestPublished()
                ->paginate(9)
                ->withQueryString(),
        ]);
    }

    /**
     * Display one currently published Blog post.
     */
    public function show(BlogPost $blogPost): View
    {
        abort_unless(
            $blogPost->isPubliclyVisible(),
            404,
        );

        return view('pages.blog.show', [
            'blogPost' => $blogPost,
        ]);
    }
}
