<?php

use App\Services\ProductService;
use App\Support\SeedImages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Known emulator wallpaper public IDs uploaded during early testing. */
    private const EMULATOR_SAMPLE_IDS = [
        'vi7lzqobh4wxsqdqktve',
        'pfcjbwnq52gbr1hcmva8',
        'ihihxiuwb4r0muhsow7s',
        'zv6xyv4shgyuoprfx4nu',
        'g3awucucjjsfrfhklwns',
    ];

    public function up(): void
    {
        foreach (DB::table('categories')->select(['id', 'name', 'image_url'])->get() as $category) {
            $current = trim((string) ($category->image_url ?? ''));
            if ($current !== '' && ! str_contains($current, 'example.com') && ! str_contains($current, 'wikimedia.org')) {
                continue;
            }

            $replacement = SeedImages::forCategory((string) $category->name);
            if ($replacement !== null) {
                DB::table('categories')->where('id', $category->id)->update([
                    'image_url' => $replacement,
                ]);
            }
        }

        foreach (DB::table('farmers')->select(['id', 'business_name', 'cover_url'])->get() as $farmer) {
            $current = trim((string) ($farmer->cover_url ?? ''));
            if ($current !== '' && ! str_contains($current, 'wikimedia.org') && ! str_contains($current, 'example.com')) {
                continue;
            }

            $cover = SeedImages::forFarmCover((string) $farmer->business_name);
            if ($cover !== null) {
                DB::table('farmers')->where('id', $farmer->id)->update([
                    'cover_url' => $cover,
                ]);
            }
        }

        foreach (DB::table('products')->select(['id', 'name', 'description', 'image_url'])->get() as $product) {
            $photos = SeedImages::forProduct((string) $product->name);
            if ($photos === null) {
                continue;
            }

            $updates = [];

            if ($this->shouldReplaceProductImage((string) ($product->image_url ?? ''))) {
                $updates['image_url'] = json_encode($photos, JSON_UNESCAPED_SLASHES);
            }

            if (
                mb_strtolower(trim((string) $product->name), 'UTF-8') === 'sweet water'
                && mb_strtolower(trim((string) ($product->description ?? '')), 'UTF-8') === 'sdasdasd'
            ) {
                $updates['description'] = 'Chilled fruit-flavored sodas bottled for the market stall.';
            }

            if ($updates !== []) {
                DB::table('products')->where('id', $product->id)->update($updates);
            }
        }

        try {
            ProductService::forgetListPages();
        } catch (\Throwable) {
            // Cache store may not be initialized during bare schema setup.
        }
    }

    public function down(): void
    {
        DB::table('farmers')
            ->where('cover_url', 'like', 'seed/%')
            ->update(['cover_url' => null]);
    }

    private function shouldReplaceProductImage(string $raw): bool
    {
        $trimmed = trim($raw);

        if ($trimmed === '' || $trimmed === '[]' || $trimmed === 'null') {
            return true;
        }

        foreach (['wikimedia.org', 'example.com', 'unsplash.com', 'seed/products/'] as $hostOrPrefix) {
            if (str_contains($trimmed, $hostOrPrefix)) {
                return true;
            }
        }

        foreach (self::EMULATOR_SAMPLE_IDS as $sampleId) {
            if (str_contains($trimmed, $sampleId)) {
                return true;
            }
        }

        return false;
    }
};

