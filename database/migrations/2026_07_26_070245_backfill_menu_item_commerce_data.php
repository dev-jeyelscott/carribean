<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Backfill cents and missing public slugs without deleting legacy data.
     */
    public function up(): void
    {
        DB::table('menu_items')
            ->select([
                'id',
                'name',
                'slug',
                'price',
            ])
            ->orderBy('id')
            ->chunkById(
                100,
                function (Collection $menuItems): void {
                    foreach ($menuItems as $menuItem) {
                        DB::table('menu_items')
                            ->where('id', $menuItem->id)
                            ->update([
                                'price_cents' => $this->priceToCents(
                                    $menuItem->price,
                                ),
                                'slug' => filled($menuItem->slug)
                                    ? $menuItem->slug
                                    : $this->uniqueSlug(
                                        name: $menuItem->name,
                                        menuItemId: (int) $menuItem->id,
                                    ),
                            ]);
                    }
                },
                'id',
            );
    }

    /**
     * Keep the data backfill intact during rollback.
     *
     * The preceding schema migration owns the added columns and may safely
     * remove them when the complete migration batch is rolled back.
     */
    public function down(): void
    {
        //
    }

    /**
     * Convert an existing decimal database value into integer cents.
     */
    private function priceToCents(
        string|int|float|null $price,
    ): ?int {
        if ($price === null || $price === '') {
            return null;
        }

        $normalized = trim((string) $price);

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new RuntimeException(
                sprintf(
                    'Menu price "%s" cannot be converted safely.',
                    $normalized,
                ),
            );
        }

        [$whole, $fraction] = array_pad(
            explode('.', $normalized, 2),
            2,
            '',
        );

        return ((int) $whole * 100)
            + (int) str_pad($fraction, 2, '0');
    }

    /**
     * Create a unique fallback slug for an existing menu item.
     */
    private function uniqueSlug(
        string $name,
        int $menuItemId,
    ): string {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'menu-item-'.$menuItemId;
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            DB::table('menu_items')
                ->where('slug', $slug)
                ->where('id', '!=', $menuItemId)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
};
