<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Welcome back')
        ->assertSee('Create an account');
});

test('customers can authenticate and are redirected to their account', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('account.index', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('customer authentication preserves the session shopping cart', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->withSession([
            'shopping_cart.items' => [
                'existing-line' => [
                    'menu_item_id' => 10,
                    'option_ids' => [],
                    'quantity' => 2,
                ],
            ],
        ])
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response
        ->assertRedirect(route('account.index', absolute: false))
        ->assertSessionHas(
            'shopping_cart.items.existing-line.quantity',
            2,
        );
});

test('users can not authenticate with an invalid password', function (): void {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to the challenge', function (): void {
    $this->skipUnlessFortifyHas(
        Features::twoFactorAuthentication(),
    );

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
});

test('customers can not access the filament admin panel', function (): void {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertForbidden();
});

test('the configured administrator can access the filament panel', function (): void {
    $administrator = User::factory()->create([
        'email' => config('admin.seed_user.email'),
    ]);

    $this->actingAs($administrator)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertOk();
});

test('users can logout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
