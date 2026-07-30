<?php

use App\Filament\Resources\GalleryImages\Schemas\GalleryImageForm;
use App\Filament\Resources\MenuCategories\Schemas\MenuCategoryForm;
use App\Filament\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Models\GalleryImage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

/**
 * Provide a real Livewire and Filament schema context for standalone form tests.
 */
final class BrandedResourceFormSchemaHost extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * Hold standalone schema state so Get callbacks resolve normally.
     *
     * @var array<string, mixed>
     */
    public array $data = [];
}

/**
 * Collect every labelled section, including conditionally hidden sections.
 *
 * A real Livewire schema host handles state resolution, while the supplied
 * model gives relationship-backed fields the same context they receive on
 * actual Filament resource pages.
 *
 * @param  class-string  $form
 * @param  class-string<Model>  $model
 * @return array<int, Section>
 */
function brandedFormSections(string $form, string $model): array
{
    $sections = [];

    $walk = static function (array $components) use (
        &$walk,
        &$sections,
    ): void {
        foreach ($components as $component) {
            if ($component instanceof Section) {
                $sections[] = $component;
            }

            if (! method_exists($component, 'getChildSchemas')) {
                continue;
            }

            foreach (
                $component->getChildSchemas(withHidden: true) as $childSchema
            ) {
                $walk(
                    $childSchema->getComponents(
                        withHidden: true,
                    ),
                );
            }
        }
    };

    $record = new $model;

    $schema = $form::configure(
        Schema::make(
            new BrandedResourceFormSchemaHost,
        )
            ->statePath('data')
            ->model($record),
    );

    $walk(
        $schema->getComponents(
            withHidden: true,
        ),
    );

    return $sections;
}

test(
    'approved editable resources use labelled form sections',
    function (
        string $form,
        string $model,
        array $headings,
    ): void {
        $sections = brandedFormSections(
            form: $form,
            model: $model,
        );

        expect($sections)
            ->toHaveCount(count($headings))
            ->and(
                array_map(
                    static fn (Section $section): string => (string) $section
                        ->getHeading(),
                    $sections,
                ),
            )
            ->toEqualCanonicalizing($headings);
    },
)->with([
    'site settings' => [
        SiteSettingForm::class,
        SiteSetting::class,
        [
            'Setting details',
            'Setting value',
        ],
    ],
    'pages' => [
        PageForm::class,
        Page::class,
        [
            'Page content',
            'About page storytelling',
            'Search preview',
            'Publication',
        ],
    ],
    'menu categories' => [
        MenuCategoryForm::class,
        MenuCategory::class,
        [
            'Category details',
            'Display settings',
        ],
    ],
    'menu items' => [
        MenuItemForm::class,
        MenuItem::class,
        [
            'Menu item details',
            'Ordering and display',
            'Options and add-ons',
            'Menu image',
        ],
    ],
    'gallery images' => [
        GalleryImageForm::class,
        GalleryImage::class,
        [
            'Image details',
            'Gallery image',
            'Display settings',
        ],
    ],
]);

test(
    'branded image sections preserve the existing upload safeguards',
    function (
        string $form,
        string $model,
        string $directory,
    ): void {
        $upload = collect(
            brandedFormSections(
                form: $form,
                model: $model,
            ),
        )
            ->flatMap(
                static fn (Section $section): array => $section
                    ->getChildComponents(),
            )
            ->first(
                static fn (mixed $component): bool => $component
                    instanceof FileUpload,
            );

        expect($upload)
            ->toBeInstanceOf(FileUpload::class)
            ->and($upload->getDirectory())
            ->toBe($directory)
            ->and($upload->getDiskName())
            ->toBe('public')
            ->and($upload->getMaxSize())
            ->toBe(2048)
            ->and($upload->getAcceptedFileTypes())
            ->toBe([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    },
)->with([
    'menu item image' => [
        MenuItemForm::class,
        MenuItem::class,
        'menu-items',
    ],
    'gallery image' => [
        GalleryImageForm::class,
        GalleryImage::class,
        'gallery',
    ],
]);
