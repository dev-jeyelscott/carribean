<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

final class BlogController extends Controller
{
    /**
     * Display the Livewire-powered public journal.
     */
    public function index(): View
    {
        return view('pages.blog.index');
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
