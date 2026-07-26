<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Contracts\View\View;

final class FaqController extends Controller
{
    /**
     * Display all visible FAQs in their administrator-defined order.
     */
    public function index(): View
    {
        return view('pages.faq.index', [
            'faqs' => Faq::query()
                ->visible()
                ->ordered()
                ->get(),
        ]);
    }
}
