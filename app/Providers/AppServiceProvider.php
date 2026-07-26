<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Observers\OrderObserver;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    private const PUBLIC_SITE_SETTINGS_ATTRIBUTE = 'public-site.settings';

    private const PUBLIC_CONTENT_VISIBILITY_ATTRIBUTE = 'public-site.content-visibility';

    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services, observers, limits, and shared views.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureObservers();
        $this->configurePublicSiteViews();

        RateLimiter::for(
            'public-forms',
            function (Request $request): Limit {
                return Limit::perMinute(5)
                    ->by($request->ip());
            },
        );
    }

    /**
     * Configure secure default application behaviors.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }

    /**
     * Register domain observers in one discoverable location.
     */
    private function configureObservers(): void
    {
        Order::observe(
            OrderObserver::class,
        );
    }

    /**
     * Share cached settings and public-content visibility with templates.
     */
    private function configurePublicSiteViews(): void
    {
        ViewFacade::composer(
            [
                'pages.*',
                'components.public.header',
                'components.public.footer',
            ],
            function (View $view): void {
                $request = request();

                if (
                    ! $request->attributes->has(
                        self::PUBLIC_SITE_SETTINGS_ATTRIBUTE,
                    )
                ) {
                    $request->attributes->set(
                        self::PUBLIC_SITE_SETTINGS_ATTRIBUTE,
                        SiteSetting::publicContactMap(),
                    );
                }

                if (
                    ! $request->attributes->has(
                        self::PUBLIC_CONTENT_VISIBILITY_ATTRIBUTE,
                    )
                ) {
                    $request->attributes->set(
                        self::PUBLIC_CONTENT_VISIBILITY_ATTRIBUTE,
                        [
                            'hasPublishedBlogPosts' => BlogPost::query()
                                ->published()
                                ->exists(),

                            'hasVisibleFaqs' => Faq::query()
                                ->visible()
                                ->exists(),

                            'publishedPageSlugs' => Page::query()
                                ->published()
                                ->whereIn(
                                    'slug',
                                    [
                                        'privacy-policy',
                                        'terms-and-conditions',
                                        'refund-and-cancellation-policy',
                                        'delivery-and-pickup-policy',
                                    ],
                                )
                                ->pluck('slug')
                                ->all(),
                        ],
                    );
                }

                /** @var array<string, string|null> $settings */
                $settings = $request->attributes->get(
                    self::PUBLIC_SITE_SETTINGS_ATTRIBUTE,
                );

                /**
                 * @var array{
                 *     hasPublishedBlogPosts: bool,
                 *     hasVisibleFaqs: bool,
                 *     publishedPageSlugs: list<string>
                 * } $visibility
                 */
                $visibility = $request->attributes->get(
                    self::PUBLIC_CONTENT_VISIBILITY_ATTRIBUTE,
                );

                $view->with([
                    'settings' => $settings,
                    'hasPublishedBlogPosts' => $visibility[
                        'hasPublishedBlogPosts'
                    ],
                    'hasVisibleFaqs' => $visibility[
                        'hasVisibleFaqs'
                    ],
                    'publishedPageSlugs' => $visibility[
                        'publishedPageSlugs'
                    ],
                ]);
            },
        );
    }
}
