<?php

use App\Http\Controllers\CheckoutSuccessController;
use App\Http\Controllers\PublicSite\BlogController;
use App\Http\Controllers\PublicSite\ContactController;
use App\Http\Controllers\PublicSite\FaqController;
use App\Http\Controllers\PublicSite\GalleryController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\MenuController;
use App\Http\Controllers\PublicSite\MenuItemController;
use App\Http\Controllers\PublicSite\PageController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeCheckoutCancelController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Account\OrderDetail;
use App\Livewire\Account\OrderList;
use App\Livewire\Orders\GuestOrderDetail;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', RobotsController::class)
    ->name('robots');

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/menu', [MenuController::class, 'index'])
    ->name('menu');

Route::get(
    '/menu/{menuItem:slug}',
    MenuItemController::class,
)->name('menu-items.show');

Route::view('/cart', 'pages.cart')
    ->name('cart.index');

Route::view('/checkout', 'pages.checkout')
    ->name('checkout.index');

Route::get(
    '/checkout/success/{order:public_id}',
    CheckoutSuccessController::class,
)
    ->middleware('signed')
    ->name('checkout.success');

Route::post(
    '/checkout/stripe/{order:public_id}',
    StripeCheckoutController::class,
)
    ->middleware('signed')
    ->name('checkout.stripe.create');

Route::get(
    '/checkout/stripe/cancel/{order:public_id}',
    StripeCheckoutCancelController::class,
)
    ->middleware('signed')
    ->name('checkout.stripe.cancel');

Route::post(
    '/webhooks/stripe',
    StripeWebhookController::class,
)->name('webhooks.stripe');

Route::middleware('auth')
    ->prefix('account')
    ->name('account.')
    ->group(function (): void {
        Route::view('/', 'pages.account.index')
            ->name('index');

        Route::view('/profile', 'pages.account.profile')
            ->name('profile');

        Route::livewire(
            '/orders',
            OrderList::class,
        )->name('orders.index');

        Route::livewire(
            '/orders/{order:public_id}',
            OrderDetail::class,
        )->name('orders.show');
    });

Route::livewire(
    '/orders/{order:public_id}/guest',
    GuestOrderDetail::class,
)
    ->middleware('signed')
    ->name('guest.orders.show');

Route::get(
    '/about',
    [PageController::class, 'about'],
)->name('about');

Route::get(
    '/privacy-policy',
    [PageController::class, 'privacyPolicy'],
)->name('privacy-policy');

Route::get(
    '/terms-and-conditions',
    [PageController::class, 'termsAndConditions'],
)->name('terms-and-conditions');

Route::get(
    '/refund-and-cancellation-policy',
    [PageController::class, 'refundAndCancellationPolicy'],
)->name('refund-and-cancellation-policy');

Route::get(
    '/delivery-and-pickup-policy',
    [PageController::class, 'deliveryAndPickupPolicy'],
)->name('delivery-and-pickup-policy');

Route::get(
    '/gallery',
    [GalleryController::class, 'index'],
)->name('gallery');

Route::get(
    '/blog',
    [BlogController::class, 'index'],
)->name('blog.index');

Route::get(
    '/blog/{blogPost:slug}',
    [BlogController::class, 'show'],
)->name('blog.show');

Route::get(
    '/faq',
    [FaqController::class, 'index'],
)->name('faq');

Route::get(
    '/contact',
    [ContactController::class, 'create'],
)->name('contact.create');

Route::middleware('throttle:public-forms')
    ->group(function (): void {
        Route::post(
            '/contact-inquiries',
            [ContactController::class, 'store'],
        )->name('contact-inquiries.store');
    });
