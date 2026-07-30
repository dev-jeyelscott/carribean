<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

final class PageController extends Controller
{
    /**
     * Display the published privacy policy.
     */
    public function privacyPolicy(): View
    {
        return $this->showPage('privacy-policy');
    }

    /**
     * Display the published terms and conditions.
     */
    public function termsAndConditions(): View
    {
        return $this->showPage('terms-and-conditions');
    }

    /**
     * Display the published refund and cancellation policy.
     */
    public function refundAndCancellationPolicy(): View
    {
        return $this->showPage(
            'refund-and-cancellation-policy',
        );
    }

    /**
     * Display the published delivery and pickup policy.
     */
    public function deliveryAndPickupPolicy(): View
    {
        return $this->showPage(
            'delivery-and-pickup-policy',
        );
    }

    /**
     * Resolve one generic published CMS page by its controlled slug.
     */
    private function showPage(string $slug): View
    {
        return view('pages.content-page', [
            'page' => Page::query()
                ->published()
                ->where('slug', $slug)
                ->firstOrFail(),
        ]);
    }
}
