<?php

it('renders the complete public homepage layout', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('id="main-content"', escape: false)
        ->assertSeeText('Taste the Caribbean.')
        ->assertSeeText('Feel the Islands.')
        ->assertSeeText('Order Online')
        ->assertDontSeeText('Book a Table')
        ->assertDontSeeText('Catering');
});
