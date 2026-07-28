<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\MenuItemOptionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @phpstan-type MenuOptionData array{name: string, additional_price_cents: int, sort_order: int}
 * @phpstan-type MenuOptionGroupData array{name: string, is_required: bool, minimum_selections: int, maximum_selections: int, sort_order: int, options: list<MenuOptionData>}
 * @phpstan-type MenuItemData array{name: string, description: string, price_cents: int, sort_order: int, is_featured: bool, dietary_labels: list<string>, allergen_information: string|null, option_groups: list<MenuOptionGroupData>}
 * @phpstan-type MenuCategoryData array{name: string, description: string, sort_order: int, items: list<MenuItemData>}
 */
class MenuSeeder extends Seeder
{
    private const IMAGE_SOURCE_DIRECTORY = 'seeders/images/menu';

    private const IMAGE_STORAGE_DIRECTORY = 'menu-items';

    /**
     * Map generic demo assets to their intended featured menu items.
     *
     * @var array<string, string>
     */
    private const DEMO_IMAGE_FILENAMES_BY_ITEM_SLUG = [
        'island-jerk-chicken' => 'product-image-01.png',
        'oxtail-braised-in-red-wine' => 'product-image-02.png',
        'escovitch-red-snapper' => 'product-image-03.png',
        'caribbean-seafood-curry' => 'product-image-04.png',
        'ital-coconut-curry' => 'product-image-05.png',
        'mango-passionfruit-cheesecake' => 'product-image-06.png',
    ];

    private const MAX_IMAGE_SIZE_IN_BYTES = 2 * 1024 * 1024;

    /**
     * @var array<string, string>
     */
    private const IMAGE_EXTENSIONS_BY_MIME_TYPE = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Seed the Caribbean menu catalogue and configured item options.
     */
    public function run(): void
    {
        $disk = Storage::disk('public');

        $disk->makeDirectory(self::IMAGE_STORAGE_DIRECTORY);

        foreach ($this->categories() as $categoryData) {
            $this->seedCategory($disk, $categoryData);
        }
    }

    /**
     * Return the complete Coast & Cay development menu catalogue.
     *
     * @return list<MenuCategoryData>
     */
    private function categories(): array
    {
        return [
            [
                'name' => 'Small Plates',
                'description' => 'Caribbean starters designed for sharing around the table.',
                'sort_order' => 1,
                'items' => [
                    $this->item(
                        name: 'Jerk Chicken Spring Rolls',
                        description: 'Crispy rolls filled with jerk-seasoned chicken and vegetables, served with mango-lime dipping sauce.',
                        priceCents: 1300,
                        sortOrder: 1,
                    ),
                    $this->item(
                        name: 'Saltfish Fritters',
                        description: 'Golden cod fritters with scallions, herbs, peppers, and citrus aioli.',
                        priceCents: 1200,
                        sortOrder: 2,
                    ),
                    $this->item(
                        name: 'Coconut Curry Shrimp',
                        description: 'Seared shrimp simmered in coconut curry with peppers, herbs, and grilled bread.',
                        priceCents: 1600,
                        sortOrder: 3,
                    ),
                    $this->item(
                        name: 'Trini Doubles',
                        description: 'Curried chickpeas between soft bara, finished with tamarind, cucumber chutney, and pepper sauce.',
                        priceCents: 1100,
                        sortOrder: 4,
                        dietaryLabels: ['Vegan'],
                    ),
                ],
            ],
            [
                'name' => 'Signature Entrées',
                'description' => 'Comforting island classics prepared with a polished California dining touch.',
                'sort_order' => 2,
                'items' => [
                    $this->item(
                        name: 'Island Jerk Chicken',
                        description: 'Flame-grilled jerk chicken with rice and peas, sweet plantains, and pineapple slaw.',
                        priceCents: 2400,
                        sortOrder: 1,
                        isFeatured: true,
                        optionGroups: [
                            $this->optionGroup(
                                name: 'Spice Level',
                                isRequired: true,
                                minimumSelections: 1,
                                maximumSelections: 1,
                                sortOrder: 1,
                                options: [
                                    $this->option('Mild', 0, 1),
                                    $this->option('Medium', 0, 2),
                                    $this->option('Hot', 0, 3),
                                ],
                            ),
                            $this->optionGroup(
                                name: 'Choose a Side',
                                isRequired: true,
                                minimumSelections: 1,
                                maximumSelections: 1,
                                sortOrder: 2,
                                options: [
                                    $this->option('Rice and Peas', 0, 1),
                                    $this->option('Coconut Rice', 0, 2),
                                    $this->option('Sweet Plantains', 0, 3),
                                    $this->option('Festival Bread', 0, 4),
                                ],
                            ),
                            $this->optionGroup(
                                name: 'Extras',
                                isRequired: false,
                                minimumSelections: 0,
                                maximumSelections: 3,
                                sortOrder: 3,
                                options: [
                                    $this->option('Extra Jerk Sauce', 150, 1),
                                    $this->option('Extra Plantains', 300, 2),
                                    $this->option('Add Avocado', 300, 3),
                                ],
                            ),
                        ],
                    ),
                    $this->item(
                        name: 'Oxtail Braised in Red Wine',
                        description: 'Slow-braised oxtail with butter beans, rich island gravy, and coconut rice.',
                        priceCents: 3200,
                        sortOrder: 2,
                        isFeatured: true,
                    ),
                    $this->item(
                        name: 'Brown Stew Chicken',
                        description: 'Caribbean-spiced chicken braised with tomatoes, peppers, thyme, and caramelized onions.',
                        priceCents: 2300,
                        sortOrder: 3,
                    ),
                    $this->item(
                        name: 'Curry Goat',
                        description: 'Tender goat slowly cooked with Caribbean curry, potatoes, herbs, and warm spices.',
                        priceCents: 2900,
                        sortOrder: 4,
                    ),
                    $this->item(
                        name: 'Cuban Mojo Pork',
                        description: 'Citrus-and-garlic roasted pork with black beans, rice, and pickled red onions.',
                        priceCents: 2600,
                        sortOrder: 5,
                    ),
                    $this->item(
                        name: 'Caribbean Short Rib',
                        description: 'Slow-braised beef short rib with tamarind glaze, root-vegetable mash, and crispy shallots.',
                        priceCents: 3400,
                        sortOrder: 6,
                    ),
                ],
            ],
            [
                'name' => 'From the Sea',
                'description' => 'Fresh seafood plates inspired by the islands and the California coast.',
                'sort_order' => 3,
                'items' => [
                    $this->item(
                        name: 'Escovitch Red Snapper',
                        description: 'Crispy whole snapper topped with spicy pickled peppers, carrots, onions, and citrus.',
                        priceCents: 3800,
                        sortOrder: 1,
                        isFeatured: true,
                    ),
                    $this->item(
                        name: 'Rum-Glazed Salmon',
                        description: 'Pan-seared salmon with dark-rum glaze, coconut rice, and seasonal vegetables.',
                        priceCents: 3000,
                        sortOrder: 2,
                    ),
                    $this->item(
                        name: 'Caribbean Seafood Curry',
                        description: 'Shrimp, fish, and mussels in fragrant coconut curry with herbs and steamed rice.',
                        priceCents: 3500,
                        sortOrder: 3,
                        isFeatured: true,
                        optionGroups: [
                            $this->optionGroup(
                                name: 'Spice Level',
                                isRequired: true,
                                minimumSelections: 1,
                                maximumSelections: 1,
                                sortOrder: 1,
                                options: [
                                    $this->option('Mild', 0, 1),
                                    $this->option('Medium', 0, 2),
                                    $this->option('Hot', 0, 3),
                                ],
                            ),
                            $this->optionGroup(
                                name: 'Rice',
                                isRequired: true,
                                minimumSelections: 1,
                                maximumSelections: 1,
                                sortOrder: 2,
                                options: [
                                    $this->option('Steamed Rice', 0, 1),
                                    $this->option('Coconut Rice', 0, 2),
                                    $this->option('Rice and Peas', 0, 3),
                                ],
                            ),
                        ],
                    ),
                    $this->item(
                        name: 'Grilled Mahi-Mahi',
                        description: 'Citrus-marinated mahi-mahi with mango salsa, roasted plantains, and lime butter.',
                        priceCents: 3100,
                        sortOrder: 4,
                    ),
                ],
            ],
            [
                'name' => 'Vegetarian & Plant-Based',
                'description' => 'Colorful plant-forward dishes layered with Caribbean spices and produce.',
                'sort_order' => 4,
                'items' => [
                    $this->item(
                        name: 'Ital Coconut Curry',
                        description: 'Pumpkin, chickpeas, callaloo, and seasonal vegetables in a fragrant coconut broth.',
                        priceCents: 2200,
                        sortOrder: 1,
                        isFeatured: true,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Jerk Cauliflower Steak',
                        description: 'Roasted jerk cauliflower with coconut rice, pineapple salsa, and herb sauce.',
                        priceCents: 2100,
                        sortOrder: 2,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Caribbean Rasta Pasta',
                        description: 'Creamy spiced pasta with colorful bell peppers, scallions, herbs, and plant-based Parmesan.',
                        priceCents: 2000,
                        sortOrder: 3,
                        dietaryLabels: ['Vegetarian'],
                        optionGroups: [
                            $this->optionGroup(
                                name: 'Protein',
                                isRequired: false,
                                minimumSelections: 0,
                                maximumSelections: 1,
                                sortOrder: 1,
                                options: [
                                    $this->option('No Protein', 0, 1),
                                    $this->option('Jerk Chicken', 600, 2),
                                    $this->option('Curry Shrimp', 900, 3),
                                    $this->option('Grilled Salmon', 1200, 4),
                                ],
                            ),
                        ],
                    ),
                ],
            ],
            [
                'name' => 'Sides',
                'description' => 'Classic accompaniments prepared to complete any island meal.',
                'sort_order' => 5,
                'items' => [
                    $this->item(
                        name: 'Rice and Peas',
                        description: 'Coconut rice cooked with red kidney beans, thyme, and island spices.',
                        priceCents: 700,
                        sortOrder: 1,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Sweet Fried Plantains',
                        description: 'Ripe plantains fried until golden and caramelized.',
                        priceCents: 700,
                        sortOrder: 2,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Festival Bread',
                        description: 'Lightly sweet Jamaican fried dumplings with a crisp exterior.',
                        priceCents: 700,
                        sortOrder: 3,
                        dietaryLabels: ['Vegetarian'],
                    ),
                    $this->item(
                        name: 'Callaloo Greens',
                        description: 'Tender leafy greens cooked with coconut milk, garlic, peppers, and herbs.',
                        priceCents: 800,
                        sortOrder: 4,
                        dietaryLabels: ['Vegan'],
                    ),
                ],
            ],
            [
                'name' => 'Desserts',
                'description' => 'Tropical house-made sweets for a memorable finish.',
                'sort_order' => 6,
                'items' => [
                    $this->item(
                        name: 'Rum Cake',
                        description: 'Moist Caribbean dark-rum cake with warm caramel sauce and vanilla cream.',
                        priceCents: 1200,
                        sortOrder: 1,
                        dietaryLabels: ['Vegetarian'],
                    ),
                    $this->item(
                        name: 'Coconut Bread Pudding',
                        description: 'Coconut custard bread pudding with toasted coconut and tropical-fruit compote.',
                        priceCents: 1100,
                        sortOrder: 2,
                        dietaryLabels: ['Vegetarian'],
                    ),
                    $this->item(
                        name: 'Mango Passionfruit Cheesecake',
                        description: 'Creamy cheesecake with mango, passionfruit, and a spiced biscuit crust.',
                        priceCents: 1200,
                        sortOrder: 3,
                        isFeatured: true,
                        dietaryLabels: ['Vegetarian'],
                    ),
                    $this->item(
                        name: 'Guava Tres Leches',
                        description: 'Soft milk-soaked sponge layered with guava cream and fresh berries.',
                        priceCents: 1200,
                        sortOrder: 4,
                        dietaryLabels: ['Vegetarian'],
                    ),
                ],
            ],
            [
                'name' => 'Island Drinks',
                'description' => 'Refreshing non-alcoholic drinks inspired by Caribbean fruits and spices.',
                'sort_order' => 7,
                'items' => [
                    $this->item(
                        name: 'Sorrel Ginger Cooler',
                        description: 'Hibiscus, ginger, citrus, and warm island spices served over ice.',
                        priceCents: 700,
                        sortOrder: 1,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Pineapple Mint Limeade',
                        description: 'Fresh pineapple, lime, mint, and sparkling water.',
                        priceCents: 700,
                        sortOrder: 2,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Caribbean Fruit Punch',
                        description: 'Guava, mango, pineapple, orange, and fresh lime.',
                        priceCents: 700,
                        sortOrder: 3,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Sea Moss Vanilla Shake',
                        description: 'Creamy sea moss drink blended with vanilla, cinnamon, and nutmeg.',
                        priceCents: 900,
                        sortOrder: 4,
                        dietaryLabels: ['Vegetarian'],
                    ),
                ],
            ],
        ];
    }

    /**
     * Build one menu-item data record with safe catalogue defaults.
     *
     * @param  list<string>  $dietaryLabels
     * @param  list<MenuOptionGroupData>  $optionGroups
     * @return MenuItemData
     */
    private function item(
        string $name,
        string $description,
        int $priceCents,
        int $sortOrder,
        bool $isFeatured = false,
        array $dietaryLabels = [],
        array $optionGroups = [],
    ): array {
        return [
            'name' => $name,
            'description' => $description,
            'price_cents' => $priceCents,
            'sort_order' => $sortOrder,
            'is_featured' => $isFeatured,
            'dietary_labels' => $dietaryLabels,
            'allergen_information' => null,
            'option_groups' => $optionGroups,
        ];
    }

    /**
     * Build one option-group data record and its selection constraints.
     *
     * @param  list<MenuOptionData>  $options
     * @return MenuOptionGroupData
     */
    private function optionGroup(
        string $name,
        bool $isRequired,
        int $minimumSelections,
        int $maximumSelections,
        int $sortOrder,
        array $options,
    ): array {
        return [
            'name' => $name,
            'is_required' => $isRequired,
            'minimum_selections' => $minimumSelections,
            'maximum_selections' => $maximumSelections,
            'sort_order' => $sortOrder,
            'options' => $options,
        ];
    }

    /**
     * Build one selectable option and its additional price.
     *
     * @return MenuOptionData
     */
    private function option(
        string $name,
        int $additionalPriceCents,
        int $sortOrder,
    ): array {
        return [
            'name' => $name,
            'additional_price_cents' => $additionalPriceCents,
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * Create or update one menu category and all items assigned to it.
     *
     * @param  MenuCategoryData  $categoryData
     */
    private function seedCategory(
        FilesystemAdapter $disk,
        array $categoryData,
    ): void {
        $items = $categoryData['items'];

        unset($categoryData['items']);

        $categorySlug = Str::slug($categoryData['name']);

        $category = MenuCategory::updateOrCreate(
            ['slug' => $categorySlug],
            [
                ...$categoryData,
                'slug' => $categorySlug,
                'is_visible' => true,
            ],
        );

        foreach ($items as $itemData) {
            $this->seedMenuItem($disk, $category, $itemData);
        }
    }

    /**
     * Create or update one menu item and seed its configured option groups.
     *
     * Demo images use an explicit filename mapping because generated assets
     * do not necessarily share the menu item's slug.
     *
     * @param  MenuItemData  $itemData
     */
    private function seedMenuItem(
        FilesystemAdapter $disk,
        MenuCategory $category,
        array $itemData,
    ): void {
        $optionGroups = $itemData['option_groups'];

        unset($itemData['option_groups']);

        $itemSlug = Str::slug($itemData['name']);

        $sourceFilename = self::DEMO_IMAGE_FILENAMES_BY_ITEM_SLUG[$itemSlug]
            ?? $itemSlug.'.webp';

        $imagePath = $this->storeSeedImageIfAvailable(
            disk: $disk,
            sourceFilename: $sourceFilename,
        );

        $attributes = [
            ...$itemData,
            'menu_category_id' => $category->id,
            'slug' => $itemSlug,
            'image_alt_text' => $itemData['name'].' plated at Coast & Cay.',
            'is_visible' => true,
            'is_available' => true,
            'is_purchasable' => true,
        ];

        if ($imagePath !== null) {
            $attributes['image_path'] = $imagePath;
        }

        $menuItem = MenuItem::updateOrCreate(
            [
                'menu_category_id' => $category->id,
                'slug' => $itemSlug,
            ],
            $attributes,
        );

        foreach ($optionGroups as $optionGroupData) {
            $this->seedOptionGroup($menuItem, $optionGroupData);
        }
    }

    /**
     * Create or update one option group and all of its selectable options.
     *
     * @param  MenuOptionGroupData  $optionGroupData
     */
    private function seedOptionGroup(
        MenuItem $menuItem,
        array $optionGroupData,
    ): void {
        $options = $optionGroupData['options'];

        unset($optionGroupData['options']);

        $optionGroup = MenuItemOptionGroup::updateOrCreate(
            [
                'menu_item_id' => $menuItem->id,
                'name' => $optionGroupData['name'],
            ],
            [
                ...$optionGroupData,
                'menu_item_id' => $menuItem->id,
            ],
        );

        foreach ($options as $optionData) {
            MenuItemOption::updateOrCreate(
                [
                    'menu_item_option_group_id' => $optionGroup->id,
                    'name' => $optionData['name'],
                ],
                [
                    ...$optionData,
                    'menu_item_option_group_id' => $optionGroup->id,
                    'is_available' => true,
                ],
            );
        }
    }

    /**
     * Copy an optional trusted seeder image into Laravel public storage.
     */
    private function storeSeedImageIfAvailable(
        FilesystemAdapter $disk,
        string $sourceFilename,
    ): ?string {
        $sourcePath = database_path(
            self::IMAGE_SOURCE_DIRECTORY.'/'.$sourceFilename,
        );

        if (! File::isFile($sourcePath)) {
            return null;
        }

        if (! File::isReadable($sourcePath)) {
            throw new RuntimeException(
                "Menu seed image is not readable: {$sourcePath}",
            );
        }

        $size = File::size($sourcePath);

        if ($size > self::MAX_IMAGE_SIZE_IN_BYTES) {
            throw new RuntimeException(
                sprintf(
                    'Menu seed image exceeds the 2 MB upload limit (%d bytes): %s',
                    $size,
                    $sourcePath,
                ),
            );
        }

        $mimeType = File::mimeType($sourcePath);

        if (
            ! is_string($mimeType)
            || ! array_key_exists(
                $mimeType,
                self::IMAGE_EXTENSIONS_BY_MIME_TYPE,
            )
        ) {
            throw new RuntimeException(
                sprintf(
                    'Unsupported menu seed image type "%s" for file: %s',
                    $mimeType ?: 'unknown',
                    $sourcePath,
                ),
            );
        }

        $contentHash = hash_file('sha256', $sourcePath);

        if (! is_string($contentHash)) {
            throw new RuntimeException(
                "Unable to hash menu seed image: {$sourcePath}",
            );
        }

        $destinationFilename = $contentHash.'.'.self::IMAGE_EXTENSIONS_BY_MIME_TYPE[$mimeType];
        $destinationPath = self::IMAGE_STORAGE_DIRECTORY.'/'.$destinationFilename;

        if ($disk->exists($destinationPath)) {
            return $destinationPath;
        }

        $storedPath = $disk->putFileAs(
            self::IMAGE_STORAGE_DIRECTORY,
            new HttpFile($sourcePath),
            $destinationFilename,
            ['visibility' => 'public'],
        );

        if ($storedPath === false) {
            throw new RuntimeException(
                "Unable to store menu seed image: {$destinationPath}",
            );
        }

        return $storedPath;
    }
}
