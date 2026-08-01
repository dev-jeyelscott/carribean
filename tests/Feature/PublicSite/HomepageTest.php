<?php

it('renders the complete public homepage layout without the section pager overlay', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('id="main-content"', escape: false)
        ->assertSee('data-home-section-pager', escape: false)
        ->assertSeeText('Taste the Caribbean.')
        ->assertSeeText('Feel the Islands.')
        ->assertSeeText('Order Online')
        ->assertDontSee('aria-label="Homepage sections"', escape: false)
        ->assertDontSee(
            'data-section-pager-context="home"',
            escape: false,
        )
        ->assertDontSeeText('Book a Table')
        ->assertDontSeeText('Catering');
});
