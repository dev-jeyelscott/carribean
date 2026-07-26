<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\Page;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;

/**
 * @phpstan-type SitemapUrl array{
 *     loc: string,
 *     lastmod: string|null
 * }
 */
final class SitemapController extends Controller
{
    /**
     * Return an XML sitemap containing only public, indexable content.
     */
    public function __invoke(): Response
    {
        /** @var list<SitemapUrl> $urls */
        $urls = [];

        $this->addUrl($urls, route('home'));
        $this->addUrl($urls, route('menu'));
        $this->addUrl($urls, route('gallery'));
        $this->addUrl($urls, route('contact.create'));

        $this->addUrl(
            $urls,
            route('reservation-request.create'),
        );

        $this->addPublishedPages($urls);
        $this->addVisibleMenuItems($urls);
        $this->addBlogPosts($urls);
        $this->addFaqPage($urls);

        return response()->view(
            'seo.sitemap',
            [
                'urls' => collect($urls)
                    ->unique('loc')
                    ->values(),
            ],
            200,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ],
        );
    }

    /**
     * Add controlled CMS pages that are currently published.
     *
     * @param  list<SitemapUrl>  $urls
     */
    private function addPublishedPages(array &$urls): void
    {
        $routeMap = [
            'about' => 'about',
            'privacy-policy' => 'privacy-policy',
            'terms-and-conditions' => 'terms-and-conditions',
            'refund-and-cancellation-policy' => 'refund-and-cancellation-policy',
            'delivery-and-pickup-policy' => 'delivery-and-pickup-policy',
        ];

        $pages = Page::query()
            ->published()
            ->whereIn(
                'slug',
                array_keys($routeMap),
            )
            ->get()
            ->keyBy('slug');

        foreach ($routeMap as $slug => $routeName) {
            $page = $pages->get($slug);

            if (! $page instanceof Page) {
                continue;
            }

            $this->addUrl(
                $urls,
                route($routeName),
                $page->updated_at,
            );
        }
    }

    /**
     * Add all visible public menu-item detail pages.
     *
     * @param  list<SitemapUrl>  $urls
     */
    private function addVisibleMenuItems(array &$urls): void
    {
        MenuItem::query()
            ->visible()
            ->get()
            ->each(function (MenuItem $menuItem) use (&$urls): void {
                $this->addUrl(
                    $urls,
                    route(
                        'menu-items.show',
                        $menuItem,
                    ),
                    $menuItem->updated_at,
                );
            });
    }

    /**
     * Add the journal index and every published blog post.
     *
     * @param  list<SitemapUrl>  $urls
     */
    private function addBlogPosts(array &$urls): void
    {
        $posts = BlogPost::query()
            ->published()
            ->latestPublished()
            ->get();

        if ($posts->isEmpty()) {
            return;
        }

        $this->addUrl(
            $urls,
            route('blog.index'),
            $posts->max('updated_at'),
        );

        $posts->each(function (BlogPost $blogPost) use (&$urls): void {
            $this->addUrl(
                $urls,
                route(
                    'blog.show',
                    $blogPost,
                ),
                $blogPost->updated_at,
            );
        });
    }

    /**
     * Add the FAQ page only when at least one question is visible.
     *
     * @param  list<SitemapUrl>  $urls
     */
    private function addFaqPage(array &$urls): void
    {
        $faq = Faq::query()
            ->visible()
            ->latest('updated_at')
            ->first();

        if (! $faq instanceof Faq) {
            return;
        }

        $this->addUrl(
            $urls,
            route('faq'),
            $faq->updated_at,
        );
    }

    /**
     * Add one canonical sitemap entry to the sitemap URL list.
     *
     * @param  list<SitemapUrl>  $urls
     */
    private function addUrl(
        array &$urls,
        string $location,
        ?CarbonInterface $lastModified = null,
    ): void {
        $urls[] = [
            'loc' => $location,
            'lastmod' => $lastModified?->toAtomString(),
        ];
    }
}
