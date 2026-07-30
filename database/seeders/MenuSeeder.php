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
     * Demo images reused across the complete menu catalogue.
     *
     * @var non-empty-list<string>
     */
    private const DEMO_IMAGE_FILENAMES = [
        'product-image-01.png',
        'product-image-02.png',
        'product-image-03.png',
        'product-image-04.png',
        'product-image-05.png',
        'product-image-06.png',
    ];

    /**
     * Track the next demo image assigned during the current seeder run.
     */
    private int $nextDemoImageIndex = 0;

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

        // Reset the image sequence so repeated seeding remains deterministic.
        $this->nextDemoImageIndex = 0;

        foreach ($this->categories() as $categoryData) {
            $this->seedCategory($disk, $categoryData);
        }
    }

    /**
     * Return the complete Coast & Cay development menu catalogue.
     *
     * Existing names remain stable so repeated seeding updates records instead
     * of creating duplicate slugs.
     *
     * @return list<MenuCategoryData>
     */
    private function categories(): array
    {
        return [
            [
                'name' => 'Small Plates',
                'description' => 'A few things for the middle of the table, from crisp fritters to warm bara and curried channa.',
                'sort_order' => 1,
                'items' => [
                    $this->item(
                        name: 'Jerk Chicken Spring Rolls',
                        description: 'Crisp pastry filled with jerk chicken, cabbage, carrot, and scallion, with mango-lime dipping sauce.',
                        priceCents: 1300,
                        sortOrder: 1,
                        allergenInformation: 'Contains wheat, soy, and egg.',
                    ),
                    $this->item(
                        name: 'Saltfish Fritters',
                        description: 'Salted cod fritters with scallion, thyme, sweet pepper, and lime aioli.',
                        priceCents: 1200,
                        sortOrder: 2,
                        allergenInformation: 'Contains fish and egg.',
                    ),
                    $this->item(
                        name: 'Coconut Curry Shrimp',
                        description: 'Shrimp cooked in coconut curry with tomato, sweet pepper, fresh herbs, and grilled coco bread.',
                        priceCents: 1600,
                        sortOrder: 3,
                        allergenInformation: 'Contains shellfish and wheat.',
                    ),
                    $this->item(
                        name: 'Trini Doubles',
                        description: 'Two soft bara with curried channa, tamarind sauce, cucumber chutney, and house pepper sauce.',
                        priceCents: 1100,
                        sortOrder: 4,
                        dietaryLabels: ['Vegan'],
                        allergenInformation: 'Contains wheat.',
                    ),
                ],
            ],
            [
                'name' => 'Signature Entrées',
                'description' => 'Grilled, braised, and curry dishes served with the sides that belong beside them.',
                'sort_order' => 2,
                'items' => [
                    $this->item(
                        name: 'Island Jerk Chicken',
                        description: 'Jerk-marinated chicken from the grill with rice and peas, sweet plantains, pineapple slaw, and pan gravy.',
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
                        description: 'Oxtail braised with butter beans, thyme, allspice, and red wine, served with coconut rice and rich gravy.',
                        priceCents: 3200,
                        sortOrder: 2,
                        isFeatured: true,
                    ),
                    $this->item(
                        name: 'Brown Stew Chicken',
                        description: 'Bone-in chicken browned and simmered with tomato, onion, sweet pepper, thyme, and ginger, with steamed rice.',
                        priceCents: 2300,
                        sortOrder: 3,
                    ),
                    $this->item(
                        name: 'Curry Goat',
                        description: 'Goat cooked slowly with Caribbean curry, potato, scallion, thyme, and Scotch bonnet, with rice and peas.',
                        priceCents: 2900,
                        sortOrder: 4,
                    ),
                    $this->item(
                        name: 'Cuban Mojo Pork',
                        description: 'Citrus-garlic roasted pork with black beans, white rice, pickled red onion, and pan juices.',
                        priceCents: 2600,
                        sortOrder: 5,
                    ),
                    $this->item(
                        name: 'Caribbean Short Rib',
                        description: 'Braised beef short rib with tamarind glaze, roasted root-vegetable mash, greens, and crisp shallots.',
                        priceCents: 3400,
                        sortOrder: 6,
                    ),
                ],
            ],
            [
                'name' => 'From the Sea',
                'description' => 'Fish and shellfish with bright pickles, coconut sauces, citrus, and plenty of fresh herbs.',
                'sort_order' => 3,
                'items' => [
                    $this->item(
                        name: 'Escovitch Red Snapper',
                        description: 'Crisp whole snapper with escovitch peppers, carrot, onion, vinegar, and lime, served with festival bread.',
                        priceCents: 3800,
                        sortOrder: 1,
                        isFeatured: true,
                        allergenInformation: 'Contains fish and wheat.',
                    ),
                    $this->item(
                        name: 'Rum-Glazed Salmon',
                        description: 'Pan-seared salmon with a dark-rum glaze, coconut rice, market vegetables, and charred lime.',
                        priceCents: 3000,
                        sortOrder: 2,
                        allergenInformation: 'Contains fish.',
                    ),
                    $this->item(
                        name: 'Caribbean Seafood Curry',
                        description: 'Shrimp, fish, and mussels in coconut curry with tomato, ginger, herbs, and steamed rice.',
                        priceCents: 3500,
                        sortOrder: 3,
                        isFeatured: true,
                        allergenInformation: 'Contains fish and shellfish.',
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
                        description: 'Citrus-marinated mahi-mahi with mango salsa, roasted plantains, seasonal greens, and lime butter.',
                        priceCents: 3100,
                        sortOrder: 4,
                        allergenInformation: 'Contains fish and milk.',
                    ),
                ],
            ],
            [
                'name' => 'Vegetarian & Plant-Based',
                'description' => 'Vegetable-led dishes with the same curries, herbs, peppers, and careful seasoning as the rest of the menu.',
                'sort_order' => 4,
                'items' => [
                    $this->item(
                        name: 'Ital Coconut Curry',
                        description: 'Pumpkin, chickpeas, callaloo, sweet potato, and seasonal vegetables in coconut broth with brown rice.',
                        priceCents: 2200,
                        sortOrder: 1,
                        isFeatured: true,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Jerk Cauliflower Steak',
                        description: 'Roasted jerk cauliflower with coconut rice, pineapple salsa, charred scallion sauce, and toasted seeds.',
                        priceCents: 2100,
                        sortOrder: 2,
                        dietaryLabels: ['Vegan'],
                        allergenInformation: 'Contains sesame.',
                    ),
                    $this->item(
                        name: 'Caribbean Rasta Pasta',
                        description: 'Penne in a creamy pepper sauce with bell peppers, scallion, thyme, and Parmesan.',
                        priceCents: 2000,
                        sortOrder: 3,
                        dietaryLabels: ['Vegetarian'],
                        allergenInformation: 'Contains wheat and milk. Protein add-ons may contain fish or shellfish.',
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
                'description' => 'The staples that round out a plate, available separately for sharing or adding on.',
                'sort_order' => 5,
                'items' => [
                    $this->item(
                        name: 'Rice and Peas',
                        description: 'Long-grain rice cooked with red kidney beans, coconut milk, scallion, thyme, and allspice.',
                        priceCents: 700,
                        sortOrder: 1,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Sweet Fried Plantains',
                        description: 'Ripe plantains fried until caramelized at the edges and soft in the center.',
                        priceCents: 700,
                        sortOrder: 2,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Festival Bread',
                        description: 'Lightly sweet Jamaican fried dumplings, crisp outside and tender inside.',
                        priceCents: 700,
                        sortOrder: 3,
                        dietaryLabels: ['Vegetarian'],
                        allergenInformation: 'Contains wheat.',
                    ),
                    $this->item(
                        name: 'Callaloo Greens',
                        description: 'Leafy greens cooked with coconut milk, garlic, scallion, thyme, and sweet pepper.',
                        priceCents: 800,
                        sortOrder: 4,
                        dietaryLabels: ['Vegan'],
                    ),
                ],
            ],
            [
                'name' => 'Desserts',
                'description' => 'House desserts with rum, coconut, mango, guava, and plenty of warm spice.',
                'sort_order' => 6,
                'items' => [
                    $this->item(
                        name: 'Rum Cake',
                        description: 'Dark-rum cake with brown-sugar caramel, vanilla cream, and toasted pecans.',
                        priceCents: 1200,
                        sortOrder: 1,
                        dietaryLabels: ['Vegetarian'],
                        allergenInformation: 'Contains wheat, egg, milk, and tree nuts.',
                    ),
                    $this->item(
                        name: 'Coconut Bread Pudding',
                        description: 'Warm coconut custard bread pudding with toasted coconut and pineapple compote.',
                        priceCents: 1100,
                        sortOrder: 2,
                        dietaryLabels: ['Vegetarian'],
                        allergenInformation: 'Contains wheat, egg, and milk.',
                    ),
                    $this->item(
                        name: 'Mango Passionfruit Cheesecake',
                        description: 'Baked cheesecake with mango, passionfruit curd, and a spiced biscuit crust.',
                        priceCents: 1200,
                        sortOrder: 3,
                        isFeatured: true,
                        dietaryLabels: ['Vegetarian'],
                        allergenInformation: 'Contains wheat, egg, and milk.',
                    ),
                    $this->item(
                        name: 'Guava Tres Leches',
                        description: 'Milk-soaked sponge with guava cream, fresh berries, and lime zest.',
                        priceCents: 1200,
                        sortOrder: 4,
                        dietaryLabels: ['Vegetarian'],
                        allergenInformation: 'Contains wheat, egg, and milk.',
                    ),
                ],
            ],
            [
                'name' => 'Island Drinks',
                'description' => 'Cold, alcohol-free drinks made with hibiscus, ginger, citrus, tropical fruit, and island spice.',
                'sort_order' => 7,
                'items' => [
                    $this->item(
                        name: 'Sorrel Ginger Cooler',
                        description: 'Hibiscus steeped with fresh ginger, orange peel, clove, and cinnamon, served over ice.',
                        priceCents: 700,
                        sortOrder: 1,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Pineapple Mint Limeade',
                        description: 'Pineapple, fresh lime, mint, cane sugar, and sparkling water.',
                        priceCents: 700,
                        sortOrder: 2,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Caribbean Fruit Punch',
                        description: 'Guava, mango, pineapple, orange, lime, and a dash of aromatic bitters.',
                        priceCents: 700,
                        sortOrder: 3,
                        dietaryLabels: ['Vegan'],
                    ),
                    $this->item(
                        name: 'Sea Moss Vanilla Shake',
                        description: 'Sea moss blended with milk, vanilla, cinnamon, nutmeg, and a little condensed milk.',
                        priceCents: 900,
                        sortOrder: 4,
                        dietaryLabels: ['Vegetarian'],
                        allergenInformation: 'Contains milk.',
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
        ?string $allergenInformation = null,
        array $optionGroups = [],
    ): array {
        return [
            'name' => $name,
            'description' => $description,
            'price_cents' => $priceCents,
            'sort_order' => $sortOrder,
            'is_featured' => $isFeatured,
            'dietary_labels' => $dietaryLabels,
            'allergen_information' => $allergenInformation,
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
     * Demo images are assigned in round-robin order and may be shared by
     * multiple menu items while retaining content-hashed storage paths.
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
        $sourceFilename = $this->nextDemoImageFilename();

        $imagePath = $this->storeSeedImageIfAvailable(
            disk: $disk,
            sourceFilename: $sourceFilename,
        );

        if ($imagePath === null) {
            throw new RuntimeException(
                sprintf(
                    'Required menu seed image does not exist: %s',
                    database_path(
                        self::IMAGE_SOURCE_DIRECTORY.'/'.$sourceFilename,
                    ),
                ),
            );
        }

        $attributes = [
            ...$itemData,
            'menu_category_id' => $category->id,
            'slug' => $itemSlug,
            'image_path' => $imagePath,
            'image_alt_text' => 'A plated serving of '.$itemData['name'].' from the Coast & Cay menu.',
            'is_visible' => true,
            'is_available' => true,
            'is_purchasable' => true,
        ];

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
     * Return the next demo image using deterministic round-robin assignment.
     */
    private function nextDemoImageFilename(): string
    {
        $imageCount = count(self::DEMO_IMAGE_FILENAMES);

        $filename = self::DEMO_IMAGE_FILENAMES[$this->nextDemoImageIndex % $imageCount];

        $this->nextDemoImageIndex++;

        return $filename;
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
