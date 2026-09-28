<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** The seeded categories and their pictures in public/seed/categories. */
    private const PICTURES = [
        'Vegetables' => 'vegetables.webp',
        'Fruits' => 'fruits.webp',
        'Grains' => 'grains.webp',
        'Dairy' => 'dairy.webp',
        'Herbs' => 'herbs.webp',
        'Organic' => 'organic.webp',
        'Legumes' => 'legumes.webp',
        'Spices' => 'spices.webp',
        'Mushrooms' => 'mushrooms.webp',
        'Honey' => 'honey.webp',
    ];

    /**
     * Gives existing categories their picture, unless an admin set one.
     */
    public function up(): void
    {
        foreach (self::PICTURES as $name => $picture) {
            DB::table('categories')
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->where(fn ($query) => $query->whereNull('image_url')->orWhere('image_url', ''))
                ->update(['image_url' => 'seed/categories/'.$picture]);
        }
    }

    /**
     * Takes the seeded pictures off again.
     */
    public function down(): void
    {
        DB::table('categories')
            ->where('image_url', 'like', 'seed/categories/%')
            ->update(['image_url' => null]);
    }
};
