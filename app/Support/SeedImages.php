<?php

namespace App\Support;

/**
 * Local seed image mappings synced from the frontend fix-again catalog assets.
 * Relative paths live under public/seed and are turned into full URLs by API resources.
 */
final class SeedImages
{
    /**
     * Category name (lowercase) => relative path under public/.
     *
     * @var array<string, string>
     */
    public const CATEGORIES = [
        'vegetables' => 'seed/categories/vegetables.webp',
        'fruits' => 'seed/categories/fruits.webp',
        'grains' => 'seed/categories/grains.webp',
        'dairy' => 'seed/categories/dairy.webp',
        'herbs' => 'seed/categories/herbs.webp',
        'herbs and spices' => 'seed/categories/herbs_and_spices.webp',
        'organic' => 'seed/categories/organic.webp',
        'organic products' => 'seed/categories/organic.webp',
        'legumes' => 'seed/categories/legumes.webp',
        'pulses' => 'seed/categories/pulses.webp',
        'spices' => 'seed/categories/spices.webp',
        'mushrooms' => 'seed/categories/mushrooms.webp',
        'honey' => 'seed/categories/honey.webp',
    ];

    /**
     * Farm business name (lowercase) => cover image path under public/.
     *
     * @var array<string, string>
     */
    public const FARM_COVERS = [
        'green leaf farm' => 'seed/farms/green_valley_cover.webp',
        'riverside orchard' => 'seed/categories/fruits.webp',
        'red river grains' => 'seed/categories/grains.webp',
        'morning herb garden' => 'seed/categories/herbs_and_spices.webp',
        'han river produce' => 'seed/farms/cedar_creek_cover.webp',
        'coastal greens' => 'seed/categories/vegetables.webp',
        'fort fresh farm' => 'seed/categories/mushrooms.webp',
        'chandni spice garden' => 'seed/categories/spices.webp',
        'kalasipalya organics' => 'seed/categories/organic.webp',
        'koyambedu harvest' => 'seed/farms/heritage_roots_cover.webp',
        'lindsay street dairy' => 'seed/farms/hillside_dairy_cover.webp',
        'charminar honey house' => 'seed/categories/honey.webp',
        'green valley farm' => 'seed/farms/green_valley_cover.webp',
        'cedar creek orchard' => 'seed/farms/cedar_creek_cover.webp',
        'hillside dairy' => 'seed/farms/hillside_dairy_cover.webp',
        'heritage roots' => 'seed/farms/heritage_roots_cover.webp',
        'nông trại lê văn nông' => 'seed/farms/green_valley_cover.webp',
        'vườn trái cây phạm thị vườn' => 'seed/farms/cedar_creek_cover.webp',
    ];

    /**
     * Product name (lowercase) => ordered list of image paths under public/.
     *
     * @var array<string, array<int, string>>
     */
    public const PRODUCTS = [
        // Main 57 seeded catalog products + live extras
        'water spinach' => ['seed/products/spinach.webp', 'seed/products/water-spinach-2.jpg'],
        'bok choy' => ['seed/products/bok-choy-1.jpg', 'seed/products/bok-choy-2.jpg'],
        'lettuce' => ['seed/products/lettuce-1.jpg', 'seed/products/lettuce-2.jpg'],
        'cucumber' => ['seed/products/cucumbers.webp'],
        'spring onion' => ['seed/products/spring-onion.jpg'],
        'choy sum' => ['seed/products/choy-sum-1.jpg', 'seed/products/choy-sum-2.jpg'],

        'hoa loc mango' => ['seed/products/hoa-loc-mango.jpg'],
        'watermelon' => ['seed/products/watermelon.jpg'],
        'ripe banana' => ['seed/products/ripe-banana.jpg'],
        'dragon fruit' => ['seed/products/dragon-fruit.jpg'],
        'papaya' => ['seed/products/papaya.jpg'],
        'guava' => ['seed/products/guava.jpg'],

        'st25 rice' => ['seed/products/st25-rice.jpg'],
        'jasmine rice' => ['seed/products/jasmine-rice.jpg'],
        'glutinous rice' => ['seed/products/glutinous-rice.jpg'],
        'mung beans' => ['seed/products/mung-beans.jpg'],
        'red beans' => ['seed/products/red-beans.jpg'],

        'basil' => ['seed/products/basil.webp'],
        'cilantro' => ['seed/products/cilantro.jpg'],
        'perilla' => ['seed/products/perilla.jpg'],
        'lemongrass' => ['seed/products/lemongrass.jpg'],
        'vietnamese coriander' => ['seed/products/vietnamese-coriander.jpg'],
        'mint' => ['seed/products/mint.jpg'],

        'king orange' => ['seed/products/king-orange.jpg'],
        'green pomelo' => ['seed/products/green-pomelo.jpg'],
        'longan' => ['seed/products/longan.jpg'],
        'carrot' => ['seed/products/carrots.webp'],
        'pumpkin' => ['seed/products/pumpkin.jpg'],
        'cherry tomato' => ['seed/products/cherry-tomato.jpg', 'seed/products/tomatoes.webp'],

        'cabbage' => ['seed/products/cabbage.jpg'],
        'cauliflower' => ['seed/products/cauliflower.jpg'],
        'watercress' => ['seed/products/watercress.jpg'],
        'beetroot' => ['seed/products/beetroot.jpg'],

        'oyster mushroom' => ['seed/products/oyster-mushroom.jpg'],
        'button mushroom' => ['seed/products/button-mushroom.jpg'],
        'enoki' => ['seed/products/enoki.jpg'],
        'eggplant' => ['seed/products/eggplant.jpg'],
        'okra' => ['seed/products/okra.jpg'],
        'onion' => ['seed/products/onion.jpg'],

        "bird's eye chili" => ['seed/products/birds-eye-chili.jpg'],
        'turmeric' => ['seed/products/turmeric.jpg'],
        'garlic' => ['seed/products/garlic.jpg'],
        'black pepper' => ['seed/products/black-pepper.jpg'],
        'cinnamon' => ['seed/products/cinnamon.jpg'],
        'star anise' => ['seed/products/star-anise.jpg'],

        'organic spinach' => ['seed/products/water-spinach-2.jpg', 'seed/products/spinach.webp'],
        'organic carrot' => ['seed/products/carrots.webp'],
        'organic lettuce' => ['seed/products/lettuce-1.jpg', 'seed/products/lettuce-2.jpg'],

        'potato' => ['seed/products/potato-1.jpg', 'seed/products/potato-2.jpg'],
        'tomato' => ['seed/products/tomatoes.webp'],
        'green beans' => ['seed/products/green-beans.jpg'],

        'fresh milk' => ['seed/products/milk.webp'],
        'yogurt' => ['seed/products/yogurt.webp', 'seed/products/yogurt-2.jpg'],
        'paneer' => ['seed/products/paneer.jpg'],
        'unsalted butter' => ['seed/products/unsalted-butter.jpg', 'seed/products/butter.webp'],

        'forest honey' => ['seed/products/forest-honey.jpg'],
        'beeswax' => ['seed/products/beeswax.jpg'],
        'sweet water' => ['seed/products/sweet-water-1.jpg', 'seed/products/sweet-water-2.jpg'],

        // Bundled frontend sample product names
        'heirloom tomatoes' => ['seed/products/tomatoes.webp'],
        'rainbow carrots' => ['seed/products/carrots.webp'],
        'baby spinach' => ['seed/products/spinach.webp'],
        'crisp cucumbers' => ['seed/products/cucumbers.webp'],
        'sweet bell peppers' => ['seed/products/peppers.webp'],
        'organic salad mix' => ['seed/products/salad.webp'],
        'fresh basil' => ['seed/products/basil.webp'],
        'honeycrisp apples' => ['seed/products/apples.webp'],
        'sweet strawberries' => ['seed/products/strawberries.webp'],
        'wild blueberries' => ['seed/products/blueberries.webp'],
        'ripe peaches' => ['seed/products/peaches.webp'],
        'organic apple cider' => ['seed/products/cider.webp'],
        'fresh goat cheese' => ['seed/products/goat_cheese.webp'],
        'whole milk' => ['seed/products/milk.webp'],
        'greek yogurt' => ['seed/products/yogurt.webp'],
        'farm butter' => ['seed/products/butter.webp'],
        'free-range eggs' => ['seed/products/eggs.webp'],
        'stone-ground oats' => ['seed/products/oats.webp'],
        'ancient grain flour' => ['seed/products/flour.webp'],
        'red lentils' => ['seed/products/lentils.webp'],
        'black beans' => ['seed/products/black_beans.webp'],
        'chickpeas' => ['seed/products/chickpeas.webp'],
        'dried oregano' => ['seed/products/oregano.webp'],
        'curly kale' => ['seed/products/kale.webp'],

        // Local Vietnamese sample product names
        'rau muống' => ['seed/products/spinach.webp', 'seed/products/water-spinach-2.jpg'],
        'cà chua bi' => ['seed/products/cherry-tomato.jpg', 'seed/products/tomatoes.webp'],
        'cà chua bi hữu cơ đà lạt' => ['seed/products/cherry-tomato.jpg', 'seed/products/tomatoes.webp'],
        'xoài cát hòa lộc' => ['seed/products/hoa-loc-mango.jpg'],
        'đậu xanh hạt' => ['seed/products/mung-beans.jpg'],
        'đậu đen xanh lòng' => ['seed/products/black_beans.webp'],
        'đậu mắt đen' => ['seed/products/black_beans.webp'],
        'đậu đỏ tây' => ['seed/products/red-beans.jpg'],
        'đậu kidney' => ['seed/products/red-beans.jpg'],
        'đậu pinto' => ['seed/products/red-beans.jpg'],
        'đậu adzuki' => ['seed/products/red-beans.jpg'],
        'đậu lăng đỏ' => ['seed/products/lentils.webp'],
        'đậu gà' => ['seed/products/chickpeas.webp'],
        'đậu nành' => ['seed/products/chickpeas.webp'],
        'đậu trắng' => ['seed/products/chickpeas.webp'],
        'đậu lupin' => ['seed/products/chickpeas.webp'],
        'đậu hà lan khô' => ['seed/products/green-beans.jpg'],
        'đậu cove khô' => ['seed/products/green-beans.jpg'],
    ];

    /**
     * Keyword fallback (lowercase) => relative path under public/ for order items
     * whose product row was deleted.
     *
     * @var array<string, string>
     */
    private const KEYWORDS = [
        'tomato' => 'seed/products/tomatoes.webp',
        'carrot' => 'seed/products/carrots.webp',
        'spinach' => 'seed/products/spinach.webp',
        'cucumber' => 'seed/products/cucumbers.webp',
        'pepper' => 'seed/products/peppers.webp',
        'chili' => 'seed/products/birds-eye-chili.jpg',
        'salad' => 'seed/products/salad.webp',
        'lettuce' => 'seed/products/lettuce-1.jpg',
        'cabbage' => 'seed/products/cabbage.jpg',
        'cauliflower' => 'seed/products/cauliflower.jpg',
        'pumpkin' => 'seed/products/pumpkin.jpg',
        'potato' => 'seed/products/potato-1.jpg',
        'onion' => 'seed/products/onion.jpg',
        'okra' => 'seed/products/okra.jpg',
        'eggplant' => 'seed/products/eggplant.jpg',
        'beetroot' => 'seed/products/beetroot.jpg',
        'basil' => 'seed/products/basil.webp',
        'mint' => 'seed/products/mint.jpg',
        'coriander' => 'seed/products/vietnamese-coriander.jpg',
        'cilantro' => 'seed/products/cilantro.jpg',
        'perilla' => 'seed/products/perilla.jpg',
        'lemongrass' => 'seed/products/lemongrass.jpg',
        'herb' => 'seed/categories/herbs_and_spices.webp',
        'garlic' => 'seed/products/garlic.jpg',
        'turmeric' => 'seed/products/turmeric.jpg',
        'cinnamon' => 'seed/products/cinnamon.jpg',
        'anise' => 'seed/products/star-anise.jpg',
        'spice' => 'seed/categories/spices.webp',
        'mushroom' => 'seed/products/button-mushroom.jpg',
        'enoki' => 'seed/products/enoki.jpg',
        'honey' => 'seed/products/forest-honey.jpg',
        'beeswax' => 'seed/products/beeswax.jpg',
        'apple' => 'seed/products/apples.webp',
        'strawberr' => 'seed/products/strawberries.webp',
        'blueberr' => 'seed/products/blueberries.webp',
        'peach' => 'seed/products/peaches.webp',
        'mango' => 'seed/products/hoa-loc-mango.jpg',
        'papaya' => 'seed/products/papaya.jpg',
        'orange' => 'seed/products/king-orange.jpg',
        'pomelo' => 'seed/products/green-pomelo.jpg',
        'longan' => 'seed/products/longan.jpg',
        'guava' => 'seed/products/guava.jpg',
        'banana' => 'seed/products/ripe-banana.jpg',
        'watermelon' => 'seed/products/watermelon.jpg',
        'dragon' => 'seed/products/dragon-fruit.jpg',
        'fruit' => 'seed/categories/fruits.webp',
        'cider' => 'seed/products/cider.webp',
        'cheese' => 'seed/products/goat_cheese.webp',
        'paneer' => 'seed/products/paneer.jpg',
        'milk' => 'seed/products/milk.webp',
        'yogurt' => 'seed/products/yogurt.webp',
        'butter' => 'seed/products/unsalted-butter.jpg',
        'dairy' => 'seed/categories/dairy.webp',
        'egg' => 'seed/products/eggs.webp',
        'oat' => 'seed/products/oats.webp',
        'rice' => 'seed/products/jasmine-rice.jpg',
        'grain' => 'seed/categories/grains.webp',
        'flour' => 'seed/products/flour.webp',
        'lentil' => 'seed/products/lentils.webp',
        'bean' => 'seed/products/green-beans.jpg',
        'chickpea' => 'seed/products/chickpeas.webp',
        'oregano' => 'seed/products/oregano.webp',
        'kale' => 'seed/products/kale.webp',
    ];

    /**
     * @return array<int, string>|null
     */
    public static function forProduct(string $name): ?array
    {
        $key = mb_strtolower(trim($name), 'UTF-8');

        return self::PRODUCTS[$key] ?? null;
    }

    public static function guessForProduct(string $name): string
    {
        $exact = self::forProduct($name);
        if ($exact !== null && isset($exact[0])) {
            return $exact[0];
        }

        $lower = mb_strtolower(trim($name), 'UTF-8');
        foreach (self::KEYWORDS as $word => $path) {
            if (str_contains($lower, $word)) {
                return $path;
            }
        }

        return 'seed/categories/vegetables.webp';
    }

    public static function forFarmCover(string $businessName): ?string
    {
        $key = mb_strtolower(trim($businessName), 'UTF-8');

        return self::FARM_COVERS[$key] ?? null;
    }

    public static function forCategory(string $categoryName): ?string
    {
        $key = mb_strtolower(trim($categoryName), 'UTF-8');

        return self::CATEGORIES[$key] ?? null;
    }

    public static function absoluteUrl(?string $pathOrUrl): ?string
    {
        if (! is_string($pathOrUrl) || trim($pathOrUrl) === '') {
            return null;
        }

        $trimmed = trim($pathOrUrl);

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        return url($trimmed);
    }

    /**
     * @param  mixed  $images
     * @return array<int, string>
     */
    public static function absoluteUrls(mixed $images): array
    {
        if (! is_array($images)) {
            return [];
        }

        $result = [];

        foreach ($images as $image) {
            $resolved = is_string($image) ? self::absoluteUrl($image) : null;
            if ($resolved !== null) {
                $result[] = $resolved;
            }
        }

        return $result;
    }
}

