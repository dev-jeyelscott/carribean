<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

final class PageController extends Controller
{
    /**
     * Display the published About page.
     */
    public function about(): View
    {
        return view('pages.content-page', [
            'page' => Page::query()
                ->published()
                ->where('slug', 'about')
                ->firstOrFail(),
        ]);
    }
}
