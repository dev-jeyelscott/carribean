<?php

test('customer login exposes only scoped authentication methods', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSeeText('Email address')
        ->assertSeeText('Password')
        ->assertDontSeeText('Sign in with a passkey')
        ->assertDontSee('resources/js/passkeys.js', false);
});
