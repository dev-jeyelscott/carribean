<?php

use App\Filament\Resources\MenuItems\Pages\CreateMenuItem;
use App\Models\MenuCategory;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set(
        'admin.seed_user.email',
        'admin@coastandcay.test',
    );

    $admin = User::factory()->create([
        'email' => 'admin@coastandcay.test',
    ]);

    $this->actingAs($admin);

    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );
});

test('an administrator can create an item with nested options', function (): void {
    $category = MenuCategory::query()->create([
        'name' => 'Main Courses',
        'slug' => 'main-courses',
        'description' => 'Generous plates.',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    Livewire::test(CreateMenuItem::class)
        ->fillForm([
            'menu_category_id' => $category->id,
            'name' => 'Jerk Chicken',
            'slug' => 'jerk-chicken',
            'description' => 'Jerk-spiced chicken.',
            'price_cents' => '18.50',
            'sort_order' => 1,
            'is_visible' => true,
            'is_featured' => true,
            'is_available' => true,
            'is_purchasable' => true,
            'dietary_labels' => [
                'Gluten-aware',
            ],
            'allergen_information' => 'Contains soy.',
            'optionGroups' => [
                [
                    'name' => 'Spice Level',
                    'is_required' => true,
                    'minimum_selections' => 1,
                    'maximum_selections' => 1,
                    'options' => [
                        [
                            'name' => 'Mild',
                            'additional_price_cents' => '0.00',
                            'is_available' => true,
                        ],
                        [
                            'name' => 'Hot',
                            'additional_price_cents' => '0.00',
                            'is_available' => true,
                        ],
                    ],
                ],
                [
                    'name' => 'Side',
                    'is_required' => true,
                    'minimum_selections' => 1,
                    'maximum_selections' => 1,
                    'options' => [
                        [
                            'name' => 'Rice and Peas',
                            'additional_price_cents' => '0.00',
                            'is_available' => true,
                        ],
                        [
                            'name' => 'Plantain',
                            'additional_price_cents' => '2.00',
                            'is_available' => true,
                        ],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('menu_items', [
        'name' => 'Jerk Chicken',
        'slug' => 'jerk-chicken',
        'price_cents' => 1850,
        'price' => '18.50',
        'is_featured' => true,
        'is_available' => true,
        'is_purchasable' => true,
    ]);

    $this->assertDatabaseHas(
        'menu_item_option_groups',
        [
            'name' => 'Spice Level',
            'is_required' => true,
            'minimum_selections' => 1,
            'maximum_selections' => 1,
        ],
    );

    $this->assertDatabaseHas(
        'menu_item_options',
        [
            'name' => 'Plantain',
            'additional_price_cents' => 200,
            'is_available' => true,
        ],
    );
});
