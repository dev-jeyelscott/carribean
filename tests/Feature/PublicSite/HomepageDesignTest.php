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
