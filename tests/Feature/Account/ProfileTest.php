<?php

use App\Livewire\Account\Profile;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('guests are redirected from customer account pages', function (): void {
    $this->get(route('account.index'))
        ->assertRedirect(route('login'));

    $this->get(route('account.profile'))
        ->assertRedirect(route('login'));

    $this->get(route('account.orders.index'))
        ->assertRedirect(route('login'));
});

test('authenticated customers can view the account overview', function (): void {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('account.index'))
        ->assertOk()
        ->assertSee('Welcome back')
        ->assertSee('Account snapshot')
        ->assertSee('Orders placed')
        ->assertSee('Active orders')
        ->assertSee('Your next island favorite is waiting');
});

test('authenticated customers can view the profile interface', function (): void {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('account.profile'))
        ->assertOk()
        ->assertSee('Your details')
        ->assertSee('Checkout defaults')
        ->assertSee('Contact information');
});

test('authenticated customers can view the empty order history', function (): void {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('account.orders.index'))
        ->assertOk()
        ->assertSee('Your first order is still ahead');
});

test('customers can update their profile', function (): void {
    $customer = User::factory()->create([
        'name' => 'Original Name',
        'phone' => '+1 555 100 2000',
    ]);

    Livewire::actingAs($customer)
        ->test(Profile::class)
        ->set('name', 'Updated Customer')
        ->set('email', $customer->email)
        ->set('phone', '+1 555 300 4000')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Your profile has been updated.');

    $this->assertDatabaseHas('users', [
        'id' => $customer->id,
        'name' => 'Updated Customer',
        'email' => $customer->email,
        'phone' => '+1 555 300 4000',
    ]);
});

test('customers can discard unsaved profile changes', function (): void {
    $customer = User::factory()->create([
        'name' => 'Original Name',
        'phone' => '+1 555 100 2000',
    ]);

    Livewire::actingAs($customer)
        ->test(Profile::class)
        ->set('name', 'Unsaved Name')
        ->set('email', 'unsaved@example.com')
        ->set('phone', '+1 555 999 9999')
        ->call('resetForm')
        ->assertSet('name', 'Original Name')
        ->assertSet('email', $customer->email)
        ->assertSet('phone', '+1 555 100 2000')
        ->assertHasNoErrors();
});

test('changing email clears verification and sends a new link', function (): void {
    Notification::fake();

    $customer = User::factory()->create([
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    Livewire::actingAs($customer)
        ->test(Profile::class)
        ->set('name', $customer->name)
        ->set('email', 'new@example.com')
        ->set('phone', $customer->phone)
        ->call('save')
        ->assertHasNoErrors();

    $customer->refresh();

    expect($customer)
        ->email->toBe('new@example.com')
        ->email_verified_at->toBeNull();

    Notification::assertSentTo(
        $customer,
        VerifyEmail::class,
    );
});

test('customers can not use another customer email', function (): void {
    $customer = User::factory()->create();

    $otherCustomer = User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    Livewire::actingAs($customer)
        ->test(Profile::class)
        ->set('name', $customer->name)
        ->set('email', $otherCustomer->email)
        ->set('phone', $customer->phone)
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

test('phone remains required when updating a profile', function (): void {
    $customer = User::factory()->create();

    Livewire::actingAs($customer)
        ->test(Profile::class)
        ->set('name', $customer->name)
        ->set('email', $customer->email)
        ->set('phone', '')
        ->call('save')
        ->assertHasErrors(['phone' => 'required']);
});
