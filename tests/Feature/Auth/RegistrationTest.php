<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('registration screen can be rendered', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Create your account')
        ->assertSee('Phone number');
});

test('public customers can register', function (): void {
    Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Jordan Campbell',
        'email' => 'jordan@example.com',
        'phone' => '+1 555 123 4567',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('account.index', absolute: false));

    $this->assertAuthenticated();

    $customer = User::query()
        ->where('email', 'jordan@example.com')
        ->firstOrFail();

    expect($customer)
        ->name->toBe('Jordan Campbell')
        ->phone->toBe('+1 555 123 4567')
        ->email_verified_at->toBeNull();

    Notification::assertSentTo(
        $customer,
        VerifyEmail::class,
    );
});

test('phone is required during customer registration', function (): void {
    $this->post(route('register.store'), [
        'name' => 'Jordan Campbell',
        'email' => 'jordan@example.com',
        'phone' => '',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrorsIn('phone');

    $this->assertGuest();
});

test('customer email addresses remain unique', function (): void {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $this->post(route('register.store'), [
        'name' => 'Another Customer',
        'email' => 'existing@example.com',
        'phone' => '+1 555 123 4567',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});
