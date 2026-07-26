<?php

use App\Http\Controllers\CheckoutSuccessController;
use App\Http\Controllers\PublicSite\BanquetHallController;
use App\Http\Controllers\PublicSite\ContactController;
use App\Http\Controllers\PublicSite\GalleryController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\MenuController;
use App\Http\Controllers\PublicSite\MenuItemController;
use App\Http\Controllers\PublicSite\OrderInquiryController;
use App\Http\Controllers\PublicSite\PageController;
use App\Http\Controllers\PublicSite\ReservationRequestController;
use App\Http\Controllers\StripeCheckoutCancelController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

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

        Route::view('/orders', 'pages.account.orders')
            ->name('orders.index');
    });

Route::get(
    '/about',
    [PageController::class, 'about'],
)->name('about');

Route::get(
    '/gallery',
    [GalleryController::class, 'index'],
)->name('gallery');

Route::get(
    '/banquet-hall',
    [BanquetHallController::class, 'index'],
)->name('banquet-hall');

Route::get(
    '/reservation-request',
    [ReservationRequestController::class, 'create'],
)->name('reservation-request.create');

Route::get(
    '/order-inquiry',
    [OrderInquiryController::class, 'create'],
)->name('order-inquiry.create');

Route::get(
    '/contact',
    [ContactController::class, 'create'],
)->name('contact.create');

Route::middleware('throttle:public-forms')
    ->group(function (): void {
        Route::post(
            '/reservation-requests',
            [ReservationRequestController::class, 'store'],
        )->name('reservation-requests.store');

        Route::post(
            '/order-inquiries',
            [OrderInquiryController::class, 'store'],
        )->name('order-inquiries.store');

        Route::post(
            '/contact-inquiries',
            [ContactController::class, 'store'],
        )->name('contact-inquiries.store');
    });
