<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class MenuController extends Controller
{
    /**
     * Display the public restaurant menu page.
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
