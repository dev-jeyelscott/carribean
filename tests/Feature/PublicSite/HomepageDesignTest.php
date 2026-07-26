<?php

it('renders the refreshed restaurant homepage sections', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSeeText('Explore Our Menu')
        ->assertSeeText('Flavors worth sharing')
        ->assertSeeText('Caribbean warmth. California ease.')
        ->assertSeeText("Chef's Picks")
        ->assertSeeText('Island favorites')
        ->assertSeeText('Dine your way')
        ->assertSeeText('A taste of the island')
        ->assertSeeText('We can’t wait to welcome you')
        ->assertSeeText('Good food. Good company. Good times.');
});

it('renders the homepage ordering and reservation actions', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSeeText('Order Online')
        ->assertSeeText('Reserve a Table')
        ->assertSee(route('cart.index'), false)
        ->assertSee(route('reservation-request.create'), false);
});
