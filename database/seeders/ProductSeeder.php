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
                Farmer::query()->whereKey($farmerId)->update(['cover_url' => $cover]);
            }
        });
    }

    /**
     * Each product has two photos of that food, and belongs to a farm that sells it.
     *
     * @return array<int, array{farm: string, category: string, name: string, description: string, price: int, stock: int, images: array<int, string>}>
     */
    private function catalog(): array
    {
        return [
            $this->item('Green Leaf Farm', 'Vegetables', 'Water spinach', 'Bunches of water spinach cut this morning, stems still crisp, sold by the kilogram at Ben Thanh Market.', 0.70, 42, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Ipomoea_aquatica_kz01.jpg/960px-Ipomoea_aquatica_kz01.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/1/10/N_Ipoa_D1600.JPG/960px-N_Ipoa_D1600.JPG',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Bok choy', 'Whole bok choy heads with tight white stems and dark leaves, washed and bunched for the stall.', 0.90, 28, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/Pak_choi.jpg/960px-Pak_choi.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Brassica_rapa_var._chinensis_%28leaf%29.jpg/960px-Brassica_rapa_var._chinensis_%28leaf%29.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Lettuce', 'Loose-leaf lettuce picked at dawn so the leaves stay sweet through the market day.', 0.80, 4, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Lettuce.jpg/960px-Lettuce.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/02/Lactuca_sativa_%27Lollo_Bionda%27.jpg/960px-Lactuca_sativa_%27Lollo_Bionda%27.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Cucumber', 'Firm green cucumbers, sold by the kilogram, suitable for salads or pickling the same day.', 0.65, 55, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Cucumber.jpg/960px-Cucumber.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Cucumis_sativus.jpg/960px-Cucumis_sativus.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Spring onion', 'Slender spring onions with white bulbs and long green stalks, tied in market bunches.', 0.55, 0, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Bunches_of_spring_onions_1.jpg/960px-Bunches_of_spring_onions_1.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/1/14/Scallions.jpg/960px-Scallions.jpg',
            ]),
            $this->item('Green Leaf Farm', 'Vegetables', 'Choy sum', 'Flowering choy sum with tender stems, harvested before the flowers fully open.', 0.75, 24, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Choy_sum_for_sale.tif/lossless-page1-960px-Choy_sum_for_sale.tif.png',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/Starr-170224-0933-Brassica_rapa_var_parachinensis-flowers-Hawea_Pl_Olinda-Maui_%2832567121783%29.jpg/960px-Starr-170224-0933-Brassica_rapa_var_parachinensis-flowers-Hawea_Pl_Olinda-Maui_%2832567121783%29.jpg',
            ]),

            $this->item('Riverside Orchard', 'Fruits', 'Hoa Loc mango', 'Ripe Hoa Loc mangoes with yellow flesh, picked when the fruit gives slightly to the thumb.', 2.60, 26, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/Mango_fruit_Nam_Dok_Mai.jpg/960px-Mango_fruit_Nam_Dok_Mai.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Mangos_-_single_and_halved.jpg/960px-Mangos_-_single_and_halved.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Watermelon', 'Whole watermelons with deep red flesh, sold by the fruit for the Binh Tay stall.', 0.70, 12, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0c/Citrullus_lanatus_03.JPG/960px-Citrullus_lanatus_03.JPG',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/%D8%A7%D9%84%D8%A8%D8%B7%D9%8A%D8%AE_%D8%A7%D9%84%D8%A3%D8%AD%D9%85%D8%B1.JPG/960px-%D8%A7%D9%84%D8%A8%D8%B7%D9%8A%D8%AE_%D8%A7%D9%84%D8%A3%D8%AD%D9%85%D8%B1.JPG',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Ripe banana', 'Hands of ripe bananas, yellow with a few brown speckles, ready to eat the same day.', 0.90, 48, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/Bananas_on_black_background_02.jpg/960px-Bananas_on_black_background_02.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/31/Cavendish_banana_from_Maracaibo.jpg/960px-Cavendish_banana_from_Maracaibo.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Dragon fruit', 'Red-skinned dragon fruit with white flesh and small black seeds, sold by the kilogram.', 1.80, 8, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b7/Hylocereus_undatus.jpg/960px-Hylocereus_undatus.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6a/Pitaya.jpg/960px-Pitaya.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Papaya', 'Ripe papaya, orange through the center, cut to order or sold whole.', 1.00, 0, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/Papaya_-_longitudinal_section.jpg/960px-Papaya_-_longitudinal_section.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Papaya_-_longitudinal_section_close-up_view.jpg/960px-Papaya_-_longitudinal_section_close-up_view.jpg',
            ]),
            $this->item('Riverside Orchard', 'Fruits', 'Guava', 'Fragrant guava with pink flesh, firm enough to travel from the orchard to the stall.', 1.10, 18, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/Guava_Fruit.jpg/960px-Guava_Fruit.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/8/84/Goiaba_vermelha.jpg/960px-Goiaba_vermelha.jpg',
            ]),

            $this->item('Red River Grains', 'Grains', 'ST25 rice', 'Polished ST25 rice grains, bagged to weight for household cooking.', 1.30, 80, [
                'https://upload.wikimedia.org/wikipedia/commons/f/fa/Rice_Grains_.png',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Mushqbudji_rice_grains_close-up.jpg/960px-Mushqbudji_rice_grains_close-up.jpg',
            ]),
            $this->item('Red River Grains', 'Grains', 'Jasmine rice', 'Uncooked jasmine rice with a light floral smell, sold by the kilogram.', 1.10, 64, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Thai_jasmine_rice_uncooked.jpg/960px-Thai_jasmine_rice_uncooked.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/6/63/Jasmine_-_rice_-_DSC00245.jpg/960px-Jasmine_-_rice_-_DSC00245.jpg',
            ]),
            $this->item('Red River Grains', 'Grains', 'Glutinous rice', 'Sticky rice for steaming, short opaque grains, kept dry in sealed bags.', 1.05, 36, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a1/2014_uncooked_Thai_glutinous_rice.jpg/960px-2014_uncooked_Thai_glutinous_rice.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Sticky_Rice_%2885950%29.jpg/960px-Sticky_Rice_%2885950%29.jpg',
            ]),
            $this->item('Red River Grains', 'Legumes', 'Mung beans', 'Whole dried mung beans, cleaned and sold by the kilogram for soups and sprouts.', 1.40, 40, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Mung_beans.jpg/960px-Mung_beans.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Vigna_radiata_MHNT.BOT.2009.17.4.jpg/960px-Vigna_radiata_MHNT.BOT.2009.17.4.jpg',
            ]),
            $this->item('Red River Grains', 'Legumes', 'Red beans', 'Dried red kidney beans, sorted for even size, ready to soak and cook.', 1.50, 22, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/Red_kidney_beans.jpg/960px-Red_kidney_beans.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/7/76/Red_beans.jpg',
            ]),

            $this->item('Morning Herb Garden', 'Herbs', 'Basil', 'Sweet basil sprigs with bright leaves, cut the same morning so the scent stays strong.', 0.50, 20, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/32/Ocimum_Basilicum_leaf_lighted_by_the_left.jpg/960px-Ocimum_Basilicum_leaf_lighted_by_the_left.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1e/Ocimum_basilicum_CG_NBG_LR.jpg/960px-Ocimum_basilicum_CG_NBG_LR.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Cilantro', 'Cilantro bunches with roots trimmed, sold for soups and salads the same day.', 0.40, 16, [
                'https://upload.wikimedia.org/wikipedia/commons/1/1b/Coriander.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/%28MHNT%29_Coriandrum_sativum_-_inflorescence.jpg/960px-%28MHNT%29_Coriandrum_sativum_-_inflorescence.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Perilla', 'Green perilla leaves, slightly serrated, bunched for grilling and noodle bowls.', 0.60, 9, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/4/46/Perilla_frutescens_in_Gimpo.jpg/960px-Perilla_frutescens_in_Gimpo.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Perilla_frutescens_var._crispa_002.jpg/960px-Perilla_frutescens_var._crispa_002.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Lemongrass', 'Fresh lemongrass stalks, outer leaves peeled, sold in tied bundles.', 0.50, 30, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/Cymbopogon_citratus.jpg/960px-Cymbopogon_citratus.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Cymbopogon.jpg/960px-Cymbopogon.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Vietnamese coriander', 'Rau ram leaves with a sharp citrus smell, picked before noon.', 0.55, 6, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/d/db/Vietnamese_mint.jpg/960px-Vietnamese_mint.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/4/47/Persicaria_odorata.jpg/960px-Persicaria_odorata.jpg',
            ]),
            $this->item('Morning Herb Garden', 'Herbs', 'Mint', 'Spearmint sprigs, cool and soft, sold in small bunches.', 0.45, 0, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Mint_leaves_%28Mentha_spicata%29.jpg/960px-Mint_leaves_%28Mentha_spicata%29.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Mentha_arvensis_-_p%C3%B5ldm%C3%BCnt_Keila.jpg/960px-Mentha_arvensis_-_p%C3%B5ldm%C3%BCnt_Keila.jpg',
            ]),

            $this->item('Han River Produce', 'Fruits', 'King orange', 'Sweet king oranges, heavy for their size, packed in open crates for Han Market.', 1.40, 24, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Ambersweet_oranges.jpg/960px-Ambersweet_oranges.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Oranges_-_whole-halved-segment.jpg/960px-Oranges_-_whole-halved-segment.jpg',
            ]),
            $this->item('Han River Produce', 'Fruits', 'Green pomelo', 'Thick-skinned green pomelo, pale flesh, sold whole by the fruit.', 1.60, 10, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/Pomelo.jpg/960px-Pomelo.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/6/69/Citrus_maxima.jpg/960px-Citrus_maxima.jpg',
            ]),
            $this->item('Han River Produce', 'Fruits', 'Longan', 'Fresh longan clusters, thin brown shells and clear flesh, sold by the kilogram.', 2.20, 14, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Longan.jpg/960px-Longan.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Longan_fruit_flesh_%26_skin.jpg/960px-Longan_fruit_flesh_%26_skin.jpg',
            ]),
            $this->item('Han River Produce', 'Vegetables', 'Carrot', 'Washed carrots, sold by the kilogram, tops removed so they stay firm.', 0.70, 44, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c8/Carrots.jpg/960px-Carrots.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/32/Carrots_of_many_colors.jpg/960px-Carrots_of_many_colors.jpg',
            ]),
            $this->item('Han River Produce', 'Vegetables', 'Pumpkin', 'Mature pumpkin with hard orange rind, sold whole or by the cut kilogram.', 0.80, 3, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f7/Pumpkin.jpg/960px-Pumpkin.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Growing_pumpkin_vegetable_%40home.jpg/960px-Growing_pumpkin_vegetable_%40home.jpg',
            ]),
            $this->item('Han River Produce', 'Vegetables', 'Cherry tomato', 'Punnets of ripe cherry tomatoes, red and yellow, picked when fully colored.', 1.90, 15, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/39/Cherry-Tomatoes-in-Pack.jpg/960px-Cherry-Tomatoes-in-Pack.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/Yellow_cherry_tomatoes.jpg/960px-Yellow_cherry_tomatoes.jpg',
            ]),

            $this->item('Coastal Greens', 'Vegetables', 'Cabbage', 'Dense green and red cabbage heads, outer leaves trimmed before delivery.', 0.60, 34, [
                'https://upload.wikimedia.org/wikipedia/commons/7/70/Cabbage.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/Rotkohl_%28Brassica_oleracea_convar%29.JPG/960px-Rotkohl_%28Brassica_oleracea_convar%29.JPG',
            ]),
            $this->item('Coastal Greens', 'Vegetables', 'Cauliflower', 'Tight white cauliflower curds with a short stalk, packed in single heads.', 0.90, 18, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b2/Brassica_oleracea_var._botrytis_%28Coliflor%29.jpg/960px-Brassica_oleracea_var._botrytis_%28Coliflor%29.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Starr_080103-1275_Brassica_oleracea_var._botrytis.jpg/960px-Starr_080103-1275_Brassica_oleracea_var._botrytis.jpg',
            ]),
            $this->item('Coastal Greens', 'Vegetables', 'Watercress', 'Peppery watercress bunches, kept damp so the leaves do not wilt before pickup.', 0.70, 11, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Nasturtium_officinale.jpg/960px-Nasturtium_officinale.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/8/87/Watercress_for_sale_-_geograph.org.uk_-_1327637.jpg',
            ]),
            $this->item('Coastal Greens', 'Vegetables', 'Beetroot', 'Round beetroots with the leaves cut short, deep red through the root.', 0.95, 0, [
                'https://upload.wikimedia.org/wikipedia/commons/f/f0/Beets.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f4/Beta_vulgaris.jpg/960px-Beta_vulgaris.jpg',
            ]),

            $this->item('Fort Fresh Farm', 'Mushrooms', 'Oyster mushroom', 'Grey oyster mushrooms grown in clusters, harvested while the caps are still cupped.', 1.70, 20, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fc/20210928_Pleurotus_01.jpg/960px-20210928_Pleurotus_01.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/20210928_Pleurotus_04.jpg/960px-20210928_Pleurotus_04.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Mushrooms', 'Button mushroom', 'Closed white button mushrooms, brushed clean, sold by the kilogram.', 1.50, 26, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/2016-01_Agaricus_bisporus_06.jpg/960px-2016-01_Agaricus_bisporus_06.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/2016-01_Agaricus_bisporus_07.jpg/960px-2016-01_Agaricus_bisporus_07.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Mushrooms', 'Enoki', 'Long white enoki bunches, roots trimmed, packed so the stems stay straight.', 1.45, 5, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/Enoki_mushroom.jpg/960px-Enoki_mushroom.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Salad_enoki_mushrooms.jpg/960px-Salad_enoki_mushrooms.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Vegetables', 'Eggplant', 'Glossy purple eggplants, firm and heavy, packed for the Crawford Market floor.', 0.80, 30, [
                'https://upload.wikimedia.org/wikipedia/commons/f/fb/Aubergine.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/An_eggplant_fruit_developing_on_the_plant_in_Howrah_district_of_West_Bengal%2C_India.jpg/960px-An_eggplant_fruit_developing_on_the_plant_in_Howrah_district_of_West_Bengal%2C_India.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Vegetables', 'Okra', 'Tender okra pods, picked small so they stay soft when cooked.', 1.05, 16, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/Okra.jpg/960px-Okra.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/Okra-pods%2C_photo_from_The_Encyclopedia_of_Food_by_Artemas_Ward.jpg/960px-Okra-pods%2C_photo_from_The_Encyclopedia_of_Food_by_Artemas_Ward.jpg',
            ]),
            $this->item('Fort Fresh Farm', 'Vegetables', 'Onion', 'Dry yellow onions with papery skins, sold by the kilogram.', 0.70, 50, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/93/Onion.jpg/960px-Onion.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/4/46/Onion_crop.jpg/960px-Onion_crop.jpg',
            ]),

            $this->item('Chandni Spice Garden', 'Spices', 'Bird\'s eye chili', 'Small hot bird\'s eye chilies, sold fresh by the kilogram and also dried on the stall.', 1.80, 18, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Bird%27s_eye_chili.jpg/960px-Bird%27s_eye_chili.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Man_turns_drying_chili_peppers%2C_Turkey.jpg/960px-Man_turns_drying_chili_peppers%2C_Turkey.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Turmeric', 'Fresh turmeric rhizomes and a ground batch from the same harvest.', 1.20, 40, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5b/Curcuma_longa_roots.jpg/960px-Curcuma_longa_roots.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/Kunyit_Bubuk.jpg/960px-Kunyit_Bubuk.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Garlic', 'Whole garlic bulbs and loose cloves, dried enough to store for several weeks.', 1.10, 60, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Garlic_bulbs_and_cloves.jpg/960px-Garlic_bulbs_and_cloves.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b7/Knoblauch_%28Allium_sativum%29-20200621-RM-085344.jpg/960px-Knoblauch_%28Allium_sativum%29-20200621-RM-085344.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Black pepper', 'Black peppercorns and fresh pepper spikes, sold by the kilogram.', 3.60, 12, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/31/Black_Pepper_%28Piper_nigrum%29_fruits.jpg/960px-Black_Pepper_%28Piper_nigrum%29_fruits.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Piper_nigrum_-_Fruits_frais_-_Penja%2C_Cameroun.jpg/960px-Piper_nigrum_-_Fruits_frais_-_Penja%2C_Cameroun.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Cinnamon', 'Rolled cinnamon quills, broken to order, with a warm sweet bark smell.', 2.80, 9, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Canelle_Cinnamomum_burmanni_Luc_Viatour.jpg/960px-Canelle_Cinnamomum_burmanni_Luc_Viatour.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/6/63/Cinnamon.jpg/960px-Cinnamon.jpg',
            ]),
            $this->item('Chandni Spice Garden', 'Spices', 'Star anise', 'Whole dried star anise, eight-point pods, kept in closed tins on the stall.', 3.40, 7, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Dried_Star_Anise_Fruit_Seeds.jpg/960px-Dried_Star_Anise_Fruit_Seeds.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/6/61/Star_anise.jpg/960px-Star_anise.jpg',
            ]),

            $this->item('Kalasipalya Organics', 'Organic', 'Organic spinach', 'Organic spinach leaves from a kitchen garden bed, cut and bunched the same morning.', 0.95, 14, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Spinach_leaves.jpg/960px-Spinach_leaves.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/From_Garden_to_Plate-_A_glimpse_of_spinach_flourishing_in_a_simple_organic_kitchen_garden._Embracing_the_magic_of_self-sufficiency%2C_where_a_small_space_becomes_a_haven_for_fresh_produce..jpg/960px-thumbnail.jpg',
            ]),
            $this->item('Kalasipalya Organics', 'Organic', 'Organic carrot', 'Organic carrots with the soil brushed off, sold by the kilogram with a harvest note.', 1.05, 20, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c8/Carrots.jpg/960px-Carrots.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/32/Carrots_of_many_colors.jpg/960px-Carrots_of_many_colors.jpg',
            ]),
            $this->item('Kalasipalya Organics', 'Organic', 'Organic lettuce', 'Organic loose-leaf lettuce, unsprayed, packed in open crates for KR Market.', 1.10, 4, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Lettuce.jpg/960px-Lettuce.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7b/Lactuca_sativa_%27Ashbrook%27.jpg/960px-Lactuca_sativa_%27Ashbrook%27.jpg',
            ]),
            $this->item('Koyambedu Harvest', 'Vegetables', 'Potato', 'Washed potatoes for wholesale crates, even size, eyes trimmed.', 0.65, 90, [
                'https://upload.wikimedia.org/wikipedia/commons/f/f3/Potatoes.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/7/77/Potato_tubers.jpg/960px-Potato_tubers.jpg',
            ]),
            $this->item('Koyambedu Harvest', 'Vegetables', 'Tomato', 'Ripe red tomatoes, firm enough for the overnight trip to Koyambedu.', 0.80, 40, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f5/Tomatoes.jpg/960px-Tomatoes.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Organic_home-grown_tomatoes_-_unripe_to_ripe.jpg/960px-Organic_home-grown_tomatoes_-_unripe_to_ripe.jpg',
            ]),
            $this->item('Koyambedu Harvest', 'Legumes', 'Green beans', 'Straight green beans, snapped fresh, packed in wholesale sacks.', 0.95, 32, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Green_beans.jpg/960px-Green_beans.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/Phaseolus_vulgaris.jpg/960px-Phaseolus_vulgaris.jpg',
            ]),

            $this->item('Lindsay Street Dairy', 'Dairy', 'Fresh milk', 'Chilled fresh milk in glass measures, bottled the same morning for New Market.', 1.30, 24, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/38/Glass_of_milk.jpg/960px-Glass_of_milk.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/A_person_is_pouring_white_milk_into_a_glass_cup.jpg/960px-A_person_is_pouring_white_milk_into_a_glass_cup.jpg',
            ]),
            $this->item('Lindsay Street Dairy', 'Dairy', 'Yogurt', 'Plain set yogurt, unsweetened, sold in cups for the stall.', 1.10, 18, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Yogurt.jpg/960px-Yogurt.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/9/96/Lakh_au_yaourt.jpg',
            ]),
            $this->item('Lindsay Street Dairy', 'Dairy', 'Paneer', 'Fresh paneer blocks, pressed the same day, mild and firm.', 6.40, 6, [
                'https://upload.wikimedia.org/wikipedia/commons/2/20/Paneer.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Panir_Paneer_Indian_cheese_fresh.jpg/960px-Panir_Paneer_Indian_cheese_fresh.jpg',
            ]),
            $this->item('Lindsay Street Dairy', 'Dairy', 'Unsalted butter', 'Unsalted butter pats from the morning churn, wrapped for the counter.', 4.80, 10, [
                'https://upload.wikimedia.org/wikipedia/commons/0/0d/Beurre.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/Butter_pats%2C_pair_%28AM_1967.15%29.jpg/960px-Butter_pats%2C_pair_%28AM_1967.15%29.jpg',
            ]),

            $this->item('Charminar Honey House', 'Honey', 'Forest honey', 'Raw forest honey, poured thick, bottled in glass for Laad Bazaar.', 7.20, 12, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/Runny_hunny.jpg/960px-Runny_hunny.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/d/da/Small_Honey_Jar_with_Honeycomb_-_51330849013.jpg/960px-Small_Honey_Jar_with_Honeycomb_-_51330849013.jpg',
            ]),
            $this->item('Charminar Honey House', 'Honey', 'Beeswax', 'Clean beeswax blocks from drained comb, sold by weight for polish and wraps.', 6.00, 8, [
                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a8/Beeswax.jpg/960px-Beeswax.jpg',
                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/03/Beeswax_blocks.JPG/960px-Beeswax_blocks.JPG',
            ]),
        ];
    }

    /**
     * @param  array<int, string>  $images
     * @return array{farm: string, category: string, name: string, description: string, price: int, stock: int, images: array<int, string>}
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
