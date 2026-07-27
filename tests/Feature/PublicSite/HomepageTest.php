<?php

it('renders the complete public homepage layout', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('id="main-content"', escape: false)
        ->assertSeeText(
            'Island hospitality, made for the California coast.',
        );
});
