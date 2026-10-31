<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $farmers = Farmer::query()->get()->keyBy('business_name');
        $categories = Category::query()->get()->keyBy('name');

        foreach ($this->catalog() as $item) {
            $farmer = $farmers[$item['farm']];
            $category = $categories[$item['category']];

            $this->createAudited(Product::class, [
                'farmer_id' => $farmer->id,
                'category_id' => $category->id,
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'stock_qty' => $item['stock'],
                'image_url' => $item['images'],
            ]);
        }

        Product::query()->orderBy('id')->get()->groupBy('farmer_id')->each(function ($products, $farmerId): void {
            $cover = $products->first()->image_url[0] ?? null;

            if (is_string($cover) && $cover !== '') {
                Farmer::query()
                    ->whereKey($farmerId)
                    ->where(fn ($query) => $query->whereNull('cover_url')->orWhere('cover_url', ''))
                    ->update(['cover_url' => $cover]);
            }
        });
    }

    /**
     * Each product uses local seed photos matched to its name and belongs to a farm that sells it.
     *
     * @return array<int, array{farm: string, category: string, name: string, description: string, price: float, stock: int, images: array<int, string>}>
     */
    private function catalog(): array
    {
        return [
            $this->item('Green Leaf Farm', 'Vegetables', 'Water spinach', 'Bunches of water spinach cut this morning, stems still crisp, sold by the kilogram at Ben Thanh Market.', 0.70, 42, [
                'seed/products/spinach.webp',
                'seed/products/water-spinach-2.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Bok choy', 'Whole bok choy heads with tight white stems and dark leaves, washed and bunched for the stall.', 0.90, 28, [
                'seed/products/bok-choy-1.jpg',
                'seed/products/bok-choy-2.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Lettuce', 'Loose-leaf lettuce picked at dawn so the leaves stay sweet through the market day.', 0.80, 4, [
                'seed/products/lettuce-1.jpg',
                'seed/products/lettuce-2.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Cucumber', 'Firm green cucumbers, sold by the kilogram, suitable for salads or pickling the same day.', 0.65, 55, [
                'seed/products/cucumbers.webp',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Spring onion', 'Slender spring onions with white bulbs and long green stalks, tied in market bunches.', 0.55, 0, [
                'seed/products/spring-onion.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Choy sum', 'Flowering choy sum with tender stems, harvested before the flowers fully open.', 0.75, 24, [
                'seed/products/choy-sum-1.jpg',
                'seed/products/choy-sum-2.jpg',
            ]),

            $this->item('Riverside Orchard', 'Fruits', 'Hoa Loc mango', 'Ripe Hoa Loc mangoes with yellow flesh, picked when the fruit gives slightly to the thumb.', 2.60, 26, [
                'seed/products/hoa-loc-mango.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Watermelon', 'Whole watermelons with deep red flesh, sold by the fruit for the Binh Tay stall.', 0.70, 12, [
                'seed/products/watermelon.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Ripe banana', 'Hands of ripe bananas, yellow with a few brown speckles, ready to eat the same day.', 0.90, 48, [
                'seed/products/ripe-banana.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Dragon fruit', 'Red-skinned dragon fruit with white flesh and small black seeds, sold by the kilogram.', 1.80, 8, [
                'seed/products/dragon-fruit.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Papaya', 'Ripe papaya, orange through the center, cut to order or sold whole.', 1.00, 0, [
                'seed/products/papaya.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Guava', 'Fragrant guava with pink flesh, firm enough to travel from the orchard to the stall.', 1.10, 18, [
                'seed/products/guava.jpg',
            ]),

            $this->item('Red River Grains', 'Grains', 'ST25 rice', 'Polished ST25 rice grains, bagged to weight for household cooking.', 1.30, 80, [
                'seed/products/st25-rice.jpg',
            ]),
            $this->item('Red River Grains', 'Grains', 'Jasmine rice', 'Uncooked jasmine rice with a light floral smell, sold by the kilogram.', 1.10, 64, [
                'seed/products/jasmine-rice.jpg',
            ]),
            $this->item('Red River Grains', 'Grains', 'Glutinous rice', 'Sticky rice for steaming, short opaque grains, kept dry in sealed bags.', 1.05, 36, [
                'seed/products/glutinous-rice.jpg',
            ]),
            $this->item('Red River Grains', 'Legumes', 'Mung beans', 'Whole dried mung beans, cleaned and sold by the kilogram for soups and sprouts.', 1.40, 40, [
                'seed/products/mung-beans.jpg',
            ]),
            $this->item('Red River Grains', 'Legumes', 'Red beans', 'Dried red kidney beans, sorted for even size, ready to soak and cook.', 1.50, 22, [
                'seed/products/red-beans.jpg',
            ]),

            $this->item('Morning Herb Garden', 'Herbs', 'Basil', 'Sweet basil sprigs with bright leaves, cut the same morning so the scent stays strong.', 0.50, 20, [
                'seed/products/basil.webp',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Cilantro', 'Cilantro bunches with roots trimmed, sold for soups and salads the same day.', 0.40, 16, [
                'seed/products/cilantro.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Perilla', 'Green perilla leaves, slightly serrated, bunched for grilling and noodle bowls.', 0.60, 9, [
                'seed/products/perilla.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Lemongrass', 'Fresh lemongrass stalks, outer leaves peeled, sold in tied bundles.', 0.50, 30, [
                'seed/products/lemongrass.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Vietnamese coriander', 'Rau ram leaves with a sharp citrus smell, picked before noon.', 0.55, 6, [
                'seed/products/vietnamese-coriander.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Mint', 'Spearmint sprigs, cool and soft, sold in small bunches.', 0.45, 0, [
                'seed/products/mint.jpg',
            ]),

            $this->item('Han River Produce', 'Fruits', 'King orange', 'Sweet king oranges, heavy for their size, packed in open crates for Han Market.', 1.40, 24, [
                'seed/products/king-orange.jpg',
            ]),
            $this->item('Han River Produce', 'Fruits', 'Green pomelo', 'Thick-skinned green pomelo, pale flesh, sold whole by the fruit.', 1.60, 10, [
                'seed/products/green-pomelo.jpg',
            ]),
            $this->item('Han River Produce', 'Fruits', 'Longan', 'Fresh longan clusters, thin brown shells and clear flesh, sold by the kilogram.', 2.20, 14, [
                'seed/products/longan.jpg',
            ]),
            $this->item('Han River Produce', 'Vegetables', 'Carrot', 'Washed carrots, sold by the kilogram, tops removed so they stay firm.', 0.70, 44, [
                'seed/products/carrots.webp',
            ]),
            $this->item('Han River Produce', 'Vegetables', 'Pumpkin', 'Mature pumpkin with hard orange rind, sold whole or by the cut kilogram.', 0.80, 3, [
                'seed/products/pumpkin.jpg',
            ]),
            $this->item('Han River Produce', 'Vegetables', 'Cherry tomato', 'Punnets of ripe cherry tomatoes, red and yellow, picked when fully colored.', 1.90, 15, [
                'seed/products/cherry-tomato.jpg',
                'seed/products/tomatoes.webp',
            ]),

            $this->item('Coastal Greens', 'Vegetables', 'Cabbage', 'Dense green and red cabbage heads, outer leaves trimmed before delivery.', 0.60, 34, [
                'seed/products/cabbage.jpg',
            ]),
            $this->item('Coastal Greens', 'Vegetables', 'Cauliflower', 'Tight white cauliflower curds with a short stalk, packed in single heads.', 0.90, 18, [
                'seed/products/cauliflower.jpg',
            ]),
            $this->item('Coastal Greens', 'Vegetables', 'Watercress', 'Peppery watercress bunches, kept damp so the leaves do not wilt before pickup.', 0.70, 11, [
                'seed/products/watercress.jpg',
            ]),
            $this->item('Coastal Greens', 'Vegetables', 'Beetroot', 'Round beetroots with the leaves cut short, deep red through the root.', 0.95, 0, [
                'seed/products/beetroot.jpg',
            ]),

            $this->item('Fort Fresh Farm', 'Mushrooms', 'Oyster mushroom', 'Grey oyster mushrooms grown in clusters, harvested while the caps are still cupped.', 1.70, 20, [
                'seed/products/oyster-mushroom.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Mushrooms', 'Button mushroom', 'Closed white button mushrooms, brushed clean, sold by the kilogram.', 1.50, 26, [
                'seed/products/button-mushroom.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Mushrooms', 'Enoki', 'Long white enoki bunches, roots trimmed, packed so the stems stay straight.', 1.45, 5, [
                'seed/products/enoki.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Vegetables', 'Eggplant', 'Glossy purple eggplants, firm and heavy, packed for the Crawford Market floor.', 0.80, 30, [
                'seed/products/eggplant.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Vegetables', 'Okra', 'Tender okra pods, picked small so they stay soft when cooked.', 1.05, 16, [
                'seed/products/okra.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Vegetables', 'Onion', 'Dry yellow onions with papery skins, sold by the kilogram.', 0.70, 50, [
                'seed/products/onion.jpg',
            ]),

            $this->item('Chandni Spice Garden', 'Spices', 'Bird\'s eye chili', 'Small hot bird\'s eye chilies, sold fresh by the kilogram and also dried on the stall.', 1.80, 18, [
                'seed/products/birds-eye-chili.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Turmeric', 'Fresh turmeric rhizomes and a ground batch from the same harvest.', 1.20, 40, [
                'seed/products/turmeric.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Garlic', 'Whole garlic bulbs and loose cloves, dried enough to store for several weeks.', 1.10, 60, [
                'seed/products/garlic.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Black pepper', 'Black peppercorns and fresh pepper spikes, sold by the kilogram.', 3.60, 12, [
                'seed/products/black-pepper.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Cinnamon', 'Rolled cinnamon quills, broken to order, with a warm sweet bark smell.', 2.80, 9, [
                'seed/products/cinnamon.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Star anise', 'Whole dried star anise, eight-point pods, kept in closed tins on the stall.', 3.40, 7, [
                'seed/products/star-anise.jpg',
            ]),

            $this->item('Kalasipalya Organics', 'Organic', 'Organic spinach', 'Organic spinach leaves from a kitchen garden bed, cut and bunched the same morning.', 0.95, 14, [
                'seed/products/water-spinach-2.jpg',
                'seed/products/spinach.webp',
            ]),
            $this->item('Kalasipalya Organics', 'Organic', 'Organic carrot', 'Organic carrots with the soil brushed off, sold by the kilogram with a harvest note.', 1.05, 20, [
                'seed/products/carrots.webp',
            ]),
            $this->item('Kalasipalya Organics', 'Organic', 'Organic lettuce', 'Organic loose-leaf lettuce, unsprayed, packed in open crates for KR Market.', 1.10, 4, [
                'seed/products/lettuce-1.jpg',
                'seed/products/lettuce-2.jpg',
            ]),
            $this->item('Koyambedu Harvest', 'Vegetables', 'Potato', 'Washed potatoes for wholesale crates, even size, eyes trimmed.', 0.65, 90, [
                'seed/products/potato-1.jpg',
                'seed/products/potato-2.jpg',
            ]),
            $this->item('Koyambedu Harvest', 'Vegetables', 'Tomato', 'Ripe red tomatoes, firm enough for the overnight trip to Koyambedu.', 0.80, 40, [
                'seed/products/tomatoes.webp',
            ]),
            $this->item('Koyambedu Harvest', 'Legumes', 'Green beans', 'Straight green beans, snapped fresh, packed in wholesale sacks.', 0.95, 32, [
                'seed/products/green-beans.jpg',
            ]),

            $this->item('Lindsay Street Dairy', 'Dairy', 'Fresh milk', 'Chilled fresh milk in glass measures, bottled the same morning for New Market.', 1.30, 24, [
                'seed/products/milk.webp',
            ]),
            $this->item('Lindsay Street Dairy', 'Dairy', 'Yogurt', 'Plain set yogurt, unsweetened, sold in cups for the stall.', 1.10, 18, [
                'seed/products/yogurt.webp',
                'seed/products/yogurt-2.jpg',
            ]),
            $this->item('Lindsay Street Dairy', 'Dairy', 'Paneer', 'Fresh paneer blocks, pressed the same day, mild and firm.', 6.40, 6, [
                'seed/products/paneer.jpg',
            ]),
            $this->item('Lindsay Street Dairy', 'Dairy', 'Unsalted butter', 'Unsalted butter pats from the morning churn, wrapped for the counter.', 4.80, 10, [
                'seed/products/unsalted-butter.jpg',
                'seed/products/butter.webp',
            ]),

            $this->item('Charminar Honey House', 'Honey', 'Forest honey', 'Raw forest honey, poured thick, bottled in glass for Laad Bazaar.', 7.20, 12, [
                'seed/products/forest-honey.jpg',
            ]),
            $this->item('Charminar Honey House', 'Honey', 'Beeswax', 'Clean beeswax blocks from drained comb, sold by weight for polish and wraps.', 6.00, 8, [
                'seed/products/beeswax.jpg',
            ]),
        ];
    }

    /**
     * @param  array<int, string>  $images
     * @return array{farm: string, category: string, name: string, description: string, price: float, stock: int, images: array<int, string>}
     */
    private function item(string $farm, string $category, string $name, string $description, float $price, int $stock, array $images): array
    {
        return [
            'farm' => $farm,
            'category' => $category,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'images' => $images,
        ];
    }
}
