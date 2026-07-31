<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

final class MenuController extends Controller
{
    /**
     * Display the public restaurant catalogue.
     *
     * The catalogue begins directly below the shared navigation and does not
     * require a separate hero image or hero-specific database query.
     */
    public function index(): View
    {
        return view('pages.menu', [
            'page' => Page::query()
                ->where('slug', 'menu')
                ->where('is_published', true)
                ->first(),
        ]);
    }
}
