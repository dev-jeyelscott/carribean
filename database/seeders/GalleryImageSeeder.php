<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GalleryImageSeeder extends Seeder
{
    private const IMAGE_SOURCE_DIRECTORY = 'seeders/images/menu';

    private const IMAGE_STORAGE_DIRECTORY = 'gallery';

    private const MAX_IMAGE_SIZE_IN_BYTES = 2 * 1024 * 1024;

    /**
     * Map supported development image MIME types to safe extensions.
     *
     * @var array<string, string>
     */
    private const IMAGE_EXTENSIONS_BY_MIME_TYPE = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Seed a complete set of natural gallery captions and alt text.
     */
    public function run(): void
    {
        $disk = Storage::disk('public');

        $disk->makeDirectory(self::IMAGE_STORAGE_DIRECTORY);

        foreach ($this->images() as $imageData) {
            $imagePath = $this->storeSeedImage(
                disk: $disk,
                sourceFilename: $imageData['image'],
            );

            GalleryImage::query()->updateOrCreate(
                [
                    'image_path' => $imagePath,
                ],
                [
                    'title' => $imageData['title'],
                    'alt_text' => $imageData['alt_text'],
                    'image_path' => $imagePath,
                    'category' => $imageData['category'],
                    'sort_order' => $imageData['sort_order'],
                    'is_visible' => true,
                ],
            );
        }
    }

    /**
     * Return gallery records matched to the bundled development images.
     *
     * @return list<array{
     *     title: string,
     *     alt_text: string,
     *     image: string,
     *     category: string,
     *     sort_order: int
     * }>
     */
    private function images(): array
    {
        return [
            [
                'title' => 'Jerk Chicken Dinner',
                'alt_text' => 'A plated Caribbean chicken dinner with rice, vegetables, and house sauce',
                'image' => 'product-image-01.png',
                'category' => 'dish',
                'sort_order' => 1,
            ],
            [
                'title' => 'Small Plates for the Table',
                'alt_text' => 'A selection of Caribbean small plates arranged for sharing',
                'image' => 'product-image-02.png',
                'category' => 'dish',
                'sort_order' => 2,
            ],
            [
                'title' => 'Coconut Curry and Rice',
                'alt_text' => 'A bowl of coconut curry served with rice and fresh herbs',
                'image' => 'product-image-03.png',
                'category' => 'dish',
                'sort_order' => 3,
            ],
            [
                'title' => 'Something Sweet After Dinner',
                'alt_text' => 'A house dessert plated with fruit and sauce',
                'image' => 'product-image-04.png',
                'category' => 'dish',
                'sort_order' => 4,
            ],
            [
                'title' => 'Sorrel, Ginger, and Citrus',
                'alt_text' => 'A chilled red sorrel drink served over ice with citrus',
                'image' => 'product-image-05.png',
                'category' => 'drink',
                'sort_order' => 5,
            ],
            [
                'title' => 'Dinner at Coast & Cay',
                'alt_text' => 'A warmly lit restaurant table prepared for an evening meal',
                'image' => 'product-image-06.png',
                'category' => 'ambiance',
                'sort_order' => 6,
            ],
        ];
    }

    /**
     * Copy a trusted development image into public storage.
     */
    private function storeSeedImage(
        FilesystemAdapter $disk,
        string $sourceFilename,
    ): string {
        $sourcePath = database_path(
            self::IMAGE_SOURCE_DIRECTORY.'/'.$sourceFilename,
        );

        if (! File::isFile($sourcePath)) {
            throw new RuntimeException(
                "Gallery seed image does not exist: {$sourcePath}",
            );
        }

        if (! File::isReadable($sourcePath)) {
            throw new RuntimeException(
                "Gallery seed image is not readable: {$sourcePath}",
            );
        }

        $size = File::size($sourcePath);

        if ($size > self::MAX_IMAGE_SIZE_IN_BYTES) {
            throw new RuntimeException(
                sprintf(
                    'Gallery seed image exceeds the 2 MB upload limit (%d bytes): %s',
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
                    'Unsupported gallery seed image type "%s" for file: %s',
                    $mimeType ?: 'unknown',
                    $sourcePath,
                ),
            );
        }

        $contentHash = hash_file('sha256', $sourcePath);

        if (! is_string($contentHash)) {
            throw new RuntimeException(
                "Unable to hash gallery seed image: {$sourcePath}",
            );
        }

        $destinationFilename = $contentHash
            .'.'
            .self::IMAGE_EXTENSIONS_BY_MIME_TYPE[$mimeType];

        $destinationPath = self::IMAGE_STORAGE_DIRECTORY
            .'/'
            .$destinationFilename;

        if ($disk->exists($destinationPath)) {
            return $destinationPath;
        }

        $storedPath = $disk->putFileAs(
            self::IMAGE_STORAGE_DIRECTORY,
            new HttpFile($sourcePath),
            $destinationFilename,
            [
                'visibility' => 'public',
            ],
        );

        if ($storedPath === false) {
            throw new RuntimeException(
                "Unable to store gallery seed image: {$destinationPath}",
            );
        }

        return $storedPath;
    }
}
