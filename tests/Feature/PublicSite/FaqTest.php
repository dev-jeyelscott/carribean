<?php

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows visible frequently asked questions in order', function (): void {
    Faq::create([
        'question' => 'Second question',
        'answer' => '<p>Second answer.</p>',
        'sort_order' => 20,
        'is_visible' => true,
    ]);

    Faq::create([
        'question' => 'First question',
        'answer' => '<p>First answer.</p>',
        'sort_order' => 10,
        'is_visible' => true,
    ]);

    Faq::create([
        'question' => 'Hidden question',
        'answer' => '<p>Hidden answer.</p>',
        'sort_order' => 5,
        'is_visible' => false,
    ]);

    $response = $this->get(route('faq'))
        ->assertOk()
        ->assertSee('First question')
        ->assertSee('Second question')
        ->assertDontSee('Hidden question');

    $response->assertSeeInOrder([
        'First question',
        'Second question',
    ]);
});
