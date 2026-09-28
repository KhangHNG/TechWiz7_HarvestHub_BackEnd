<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\FarmerFollow;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Wishlist;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Test accounts in Mumbai that cover every case the app handles: an order in
 * every status, low stock and sold out products, and months of sales history.
 *
 * Run it on its own, after the usual seeders:
 *   php artisan db:seed --class=TestAccountsSeeder
 *
 * Customers and farmers log in with Harvest123, the admin with Admin12345.
 */
class TestAccountsSeeder extends Seeder
{
    use SeedsAudit;

    private const PASSWORD = 'Harvest123';

    private const ADMIN_PASSWORD = 'Admin12345';

    /** Markets: name, street, district, ward, latitude, longitude, hours, market days (1 is Monday). */
    private const MARKETS = [
        'colaba' => ['Colaba Market', 'Shahid Bhagat Singh Road', 'South Mumbai', 'Colaba Ward', 18.9150, 72.8277, '07:00 - 13:00', [6]],
        'crawford' => ['Crawford Market', 'DN Road', 'South Mumbai', 'Fort Ward', 18.9474, 72.8347, '10:00 - 20:00', [3, 6]],
        'chembur' => ['Chembur Market', 'RC Marg', 'Eastern Suburbs', 'Chembur Ward', 19.0522, 72.9005, '09:00 - 15:00', [7]],
    ];

    /** Customers: name, email, phone, street, district, ward. Ananya is the demo account. */
    private const CUSTOMERS = [
        'ananya' => ['Ananya Sharma', 'ananya@example.com', '0901234567', '25 Dadabhai Naoroji Road', 'South Mumbai', 'Fort Ward'],
        'arjun' => ['Arjun Mehta', 'arjun.mehta@example.com', '0934567812', '12 Marine Drive', 'South Mumbai', 'Marine Lines Ward'],
        'kavya' => ['Kavya Iyer', 'kavya.iyer@example.com', '0972153348', '8 Hill Road', 'Western Suburbs', 'Bandra Ward'],
        'siddharth' => ['Siddharth Joshi', 'siddharth.joshi@example.com', '0908871265', '41 Senapati Bapat Marg', 'Mumbai City', 'Dadar Ward'],
        'nisha' => ['Nisha Reddy', 'nisha.reddy@example.com', '0983329051', '5 Juhu Tara Road', 'Western Suburbs', 'Juhu Ward'],
        'farhan' => ['Farhan Sheikh', 'farhan.sheikh@example.com', '0356047719', '17 Maulana Azad Road', 'Mumbai City', 'Byculla Ward'],
    ];

    /** Farmers: name, email, phone, street, district, ward. */
    private const FARMERS = [
        'rajesh' => ['Rajesh Patil', 'rajesh@farm.example.com', '0912345678', 'Plot 14, Aarey Colony Road', 'Western Suburbs', 'Goregaon Ward'],
        'sunita' => ['Sunita Pawar', 'sunita@farm.example.com', '0913456789', '22 LBS Marg', 'Eastern Suburbs', 'Mulund Ward'],
        'harish' => ['Harish Naik', 'harish@farm.example.com', '0908123456', '9 Sion Trombay Road', 'Eastern Suburbs', 'Chembur Ward'],
        'imran' => ['Imran Shaikh', 'imran@farm.example.com', '0932714580', '30 Kurla Andheri Road', 'Eastern Suburbs', 'Kurla Ward'],
        'priya' => ['Priya Patel', 'priya@farm.example.com', '0984602317', '6 Ghatkopar Link Road', 'Eastern Suburbs', 'Ghatkopar Ward'],
    ];

    /** Farms: farmer, name, market, story, rating, cover photo. */
    private const FARMS = [
        'greenvalley' => ['rajesh', 'Green Valley Farm', 'colaba', 'We grow over 40 heirloom vegetables without synthetic sprays, and pick everything the morning before market.', 4.9,
            'https://images.pexels.com/photos/20313557/pexels-photo-20313557.jpeg?auto=compress&cs=tinysrgb&w=1200'],
        'cedarcreek' => ['sunita', 'Cedar Creek Orchard', 'crawford', 'Three generations of apples, berries, and stone fruit, grown on the same sunny hillside.', 4.7,
            'https://images.pexels.com/photos/28387801/pexels-photo-28387801.jpeg?auto=compress&cs=tinysrgb&w=1200'],
        'hillside' => ['harish', 'Hillside Dairy', 'chembur', 'Small-batch milk, yogurt, and cheese from our herd of 30 grass-fed goats and cows.', 4.8,
            'https://images.pexels.com/photos/34075170/pexels-photo-34075170.jpeg?auto=compress&cs=tinysrgb&w=1200'],
        'heritage' => ['imran', 'Heritage Roots', 'crawford', 'Beans, lentils, and ancient grains, dried in the sun and milled on our farm.', 4.6,
            'https://images.pexels.com/photos/20396315/pexels-photo-20396315.jpeg?auto=compress&cs=tinysrgb&w=1200'],
        'sunrise' => ['priya', 'Sunrise Acres', 'colaba', 'Leafy greens grown in rich compost soil.', 0, null],
    ];

    /** Products: farm, name, category, price in US dollars, stock, days since listed, photo, description. */
    private const PRODUCTS = [
        'tomatoes' => ['greenvalley', 'Heirloom Tomatoes', 'Vegetables', 4.50, 24, 1,
            'https://images.pexels.com/photos/8469442/pexels-photo-8469442.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Old-variety tomatoes in reds, yellows, and greens, picked ripe for the fullest flavor. Keep them on the counter, stem side down, and refrigerate only once cut.'],
        'carrots' => ['greenvalley', 'Rainbow Carrots', 'Vegetables', 3.20, 18, 2,
            'https://images.pexels.com/photos/24783851/pexels-photo-24783851.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Purple, orange, and yellow carrots with a sweet, earthy crunch. Twist off the tops and keep the roots in a bag in the fridge for up to two weeks.'],
        'spinach' => ['greenvalley', 'Baby Spinach', 'Vegetables', 2.75, 3, 1,
            'https://images.pexels.com/photos/8852027/pexels-photo-8852027.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Tender young leaves, mild enough to eat raw in a salad. Store them dry in a lidded box lined with a paper towel, and use within four days.'],
        'cucumbers' => ['greenvalley', 'Crisp Cucumbers', 'Vegetables', 1.50, 40, 3,
            'https://images.pexels.com/photos/39490165/pexels-photo-39490165.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Crisp, thin-skinned cucumbers with small seeds and no bitterness. Keep them in the warmest part of the fridge, away from bananas and tomatoes.'],
        'peppers' => ['greenvalley', 'Sweet Bell Peppers', 'Vegetables', 5.00, 0, 4,
            'https://images.pexels.com/photos/9877002/pexels-photo-9877002.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Thick-walled red and yellow peppers, sweet enough to snack on raw. Store them whole and unwashed in the fridge crisper for up to a week.'],
        'salad' => ['greenvalley', 'Organic Salad Mix', 'Vegetables', 4.25, 12, 2,
            'https://images.pexels.com/photos/4198024/pexels-photo-4198024.jpeg?auto=compress&cs=tinysrgb&w=800',
            'A ready-washed mix of baby lettuces, arugula, and herbs, cut the morning of market day. Keep it in its bag in the fridge and dress just before serving.'],
        'basil' => ['greenvalley', 'Fresh Basil', 'Herbs', 2.00, 0, 5,
            'https://images.pexels.com/photos/2575378/pexels-photo-2575378.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Fragrant sweet basil, perfect for pho, salads, and pesto. Stand the stems in a glass of water on the counter, like flowers, and keep it out of the fridge.'],
        'apples' => ['cedarcreek', 'Honeycrisp Apples', 'Fruits', 3.80, 30, 1,
            'https://images.pexels.com/photos/3746517/pexels-photo-3746517.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Juicy, crisp apples that balance sweet and tart, great for snacking and baking. They keep for weeks in the fridge; take them out an hour before eating for the best flavor.'],
        'strawberries' => ['cedarcreek', 'Sweet Strawberries', 'Fruits', 4.50, 14, 1,
            'https://images.pexels.com/photos/36646969/pexels-photo-36646969.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Sun-ripened strawberries, picked red all the way through. Keep them unwashed in the fridge and rinse just before eating, within two or three days.'],
        'blueberries' => ['cedarcreek', 'Wild Blueberries', 'Fruits', 6.00, 2, 2,
            'https://images.pexels.com/photos/17721055/pexels-photo-17721055.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Small, intensely flavored wild blueberries. Store them dry in the fridge and wash just before use; they also freeze well for smoothies.'],
        'peaches' => ['cedarcreek', 'Ripe Peaches', 'Fruits', 4.20, 0, 6,
            'https://images.pexels.com/photos/9265739/pexels-photo-9265739.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Soft, fragrant peaches that drip when you bite them. Ripen them on the counter, then move them to the fridge to slow them down.'],
        'cider' => ['cedarcreek', 'Organic Apple Cider', 'Fruits', 7.50, 10, 3,
            'https://images.pexels.com/photos/10673227/pexels-photo-10673227.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Cloudy, unfiltered juice pressed from the orchard\'s own organic apples. Keep it chilled and drink within a week of opening.'],
        'goatcheese' => ['hillside', 'Fresh Goat Cheese', 'Dairy', 6.25, 8, 2,
            'https://images.pexels.com/photos/36570893/pexels-photo-36570893.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Fresh, tangy goat cheese made in small batches each week. Keep it sealed in the fridge and enjoy within ten days.'],
        'milk' => ['hillside', 'Whole Milk', 'Dairy', 3.50, 20, 1,
            'https://images.pexels.com/photos/18258533/pexels-photo-18258533.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Creamy whole milk from grass-fed cows, bottled the day before market. Keep it cold and use within three days of opening.'],
        'yogurt' => ['hillside', 'Greek Yogurt', 'Dairy', 4.00, 2, 3,
            'https://images.pexels.com/photos/10809258/pexels-photo-10809258.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Thick, strained yogurt with a gentle tang, made from the farm\'s own milk. Keep it chilled and stir in any liquid on top before eating.'],
        'butter' => ['hillside', 'Farm Butter', 'Dairy', 5.50, 11, 4,
            'https://images.pexels.com/photos/7110152/pexels-photo-7110152.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Rich, slow-churned butter with a lightly salted finish. Keep what you are using in a covered dish on the counter and the rest in the fridge.'],
        'eggs' => ['hillside', 'Free-Range Eggs', 'Dairy', 5.00, 20, 1,
            'https://images.pexels.com/photos/4397062/pexels-photo-4397062.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Brown eggs from hens that roam outdoors all day, with deep orange yolks. Store them in their carton in the fridge, pointed end down.'],
        'oats' => ['heritage', 'Stone-Ground Oats', 'Grains', 4.80, 16, 5,
            'https://images.pexels.com/photos/27490597/pexels-photo-27490597.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Whole oats ground slowly on stone for a nutty flavor and hearty texture. Keep them in a sealed jar somewhere cool and dry.'],
        'flour' => ['heritage', 'Ancient Grain Flour', 'Grains', 5.20, 9, 6,
            'https://images.pexels.com/photos/32525228/pexels-photo-32525228.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Whole grain flour from ancient wheat varieties, great for bread and pancakes. Store it airtight in a cool cupboard, or in the freezer for longer.'],
        'lentils' => ['heritage', 'Red Lentils', 'Legumes', 3.60, 22, 3,
            'https://images.pexels.com/photos/14177776/pexels-photo-14177776.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Split red lentils that cook in about fifteen minutes into a soft, creamy dal. Keep them in a sealed jar away from light and moisture.'],
        'beans' => ['heritage', 'Black Beans', 'Legumes', 3.20, 4, 4,
            'https://images.pexels.com/photos/31672561/pexels-photo-31672561.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Glossy black beans with a rich, earthy taste. Soak them overnight before cooking and store dry beans in a sealed jar.'],
        'chickpeas' => ['heritage', 'Chickpeas', 'Legumes', 3.40, 0, 7,
            'https://images.pexels.com/photos/7717474/pexels-photo-7717474.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Plump chickpeas for curries, salads, and homemade hummus. Soak them overnight and keep dry chickpeas in a sealed jar.'],
        'oregano' => ['heritage', 'Dried Oregano', 'Herbs', 3.00, 13, 8,
            'https://images.pexels.com/photos/4871248/pexels-photo-4871248.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Oregano dried slowly in the shade to keep its oils and aroma. Store it in a sealed jar away from the stove\'s heat.'],
        'kale' => ['sunrise', 'Curly Kale', 'Vegetables', 2.50, 20, 1,
            'https://images.pexels.com/photos/51372/kale-vegetables-brassica-oleracea-var-sabellica-l-51372.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Crinkly, deep green kale that stays crisp in salads and soups. Wrap it loosely in a damp towel in the fridge and use within five days.'],
    ];

    private CarbonImmutable $now;

    /** @var array<string, Product> */
    private array $products = [];

    public function run(): void
    {
        if (User::query()->where('email', 'ananya@example.com')->exists()) {
            $this->command?->warn('The test accounts are already loaded.');

            return;
        }

        // Quiet, so loading about 1,100 orders doesn't fill the admin bell.
        Model::withoutEvents(fn () => DB::transaction(fn () => $this->load()));

        $this->command?->info('Loaded the test accounts: '
            .Order::query()->whereIn('farmer_id', Farmer::query()->whereIn('business_name', array_column(self::FARMS, 1))->pluck('id'))->count()
            .' orders for their farms.');
    }

    private function load(): void
    {
        $this->now = CarbonImmutable::now();
        mt_srand(7);

        $markets = $this->markets();
        $customers = $this->people(self::CUSTOMERS, 'CUSTOMER');
        $farmers = $this->people(self::FARMERS, 'FARMER');
        $this->admin();
        $farms = $this->farms($farmers, $markets);
        $this->products($farms);
        $this->ananya($customers['ananya'], $farms);
        $this->farmOrders($customers, $farmers['rajesh'], $farms);
    }

    /** @return array<string, Market> */
    private function markets(): array
    {
        $markets = [];
        foreach (self::MARKETS as $key => [$name, $street, $district, $ward, $latitude, $longitude, $hours]) {
            $markets[$key] = Market::query()->where('name', $name)->where('city', 'Mumbai')->first()
                ?? $this->createAudited(Market::class, [
                    'name' => $name,
                    'address' => "$street, $ward, $district, Mumbai",
                    'city' => 'Mumbai',
                    'district' => $district,
                    'ward' => $ward,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'operating_hours' => $hours,
                    'is_active' => true,
                ]);
        }

        return $markets;
    }

    /** @return array<string, User> */
    private function people(array $people, string $role): array
    {
        $password = Hash::make(self::PASSWORD);
        $users = [];
        foreach ($people as $key => [$name, $email, $phone, $street, $district, $ward]) {
            // Priya signed up yesterday; everyone else at the start of September.
            $joined = $key === 'priya' ? $this->now->subDay() : CarbonImmutable::parse('2026-09-01 09:00');
            $users[$key] = $this->createAudited(User::class, [
                'full_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $password,
                'address' => $street,
                'city' => 'Mumbai',
                'district' => $district,
                'ward' => $ward,
                'capital' => 'Mumbai',
                'avatar_url' => null,
                'role' => $role,
                'email_verified_at' => $joined,
                'created_at' => $joined,
                'updated_at' => $joined,
            ]);
        }

        return $users;
    }

    private function admin(): void
    {
        if (User::query()->where('email', 'admin@harvesthub.vn')->exists()) {
            return;
        }
        $joined = CarbonImmutable::parse('2026-09-01 09:00');
        $this->createAudited(User::class, [
            'full_name' => 'HarvestHub Admin',
            'email' => 'admin@harvesthub.vn',
            'phone' => '0938221234',
            'password_hash' => Hash::make(self::ADMIN_PASSWORD),
            'address' => '3 Veer Nariman Road',
            'city' => 'Mumbai',
            'district' => 'South Mumbai',
            'ward' => 'Churchgate Ward',
            'capital' => 'Mumbai',
            'avatar_url' => null,
            'role' => 'ADMIN',
            'email_verified_at' => $joined,
            'created_at' => $joined,
            'updated_at' => $joined,
        ]);
    }

    /** @return array<string, Farmer> */
    private function farms(array $farmers, array $markets): array
    {
        $farms = [];
        foreach (self::FARMS as $key => [$farmer, $name, $market, $story, $rating, $cover]) {
            $farms[$key] = $this->createAudited(Farmer::class, [
                'user_id' => $farmers[$farmer]->id,
                'market_id' => $markets[$market]->id,
                'business_name' => $name,
                'description' => $story,
                'rating' => $rating,
                'is_accepting_orders' => true,
                'cover_url' => $cover,
                'created_at' => $farmers[$farmer]->created_at,
                'updated_at' => $farmers[$farmer]->created_at,
            ]);
        }

        return $farms;
    }

    private function products(array $farms): void
    {
        $categories = Category::query()->pluck('id', 'name')->all();
        foreach (self::PRODUCTS as $key => [$farm, $name, $category, $price, $stock, $days, $photo, $description]) {
            $categories[$category] ??= $this->createAudited(Category::class, ['name' => $category])->id;
            $listed = $this->now->subDays($days);
            $this->products[$key] = $this->createAudited(Product::class, [
                'farmer_id' => $farms[$farm]->id,
                'category_id' => $categories[$category],
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock_qty' => $stock,
                'image_url' => [$photo],
                'created_at' => $listed,
                'updated_at' => $listed,
            ]);
        }
    }

    /** The demo customer: a cart, a wishlist, a followed farm, and an order in every status. */
    private function ananya(User $ananya, array $farms): void
    {
        // The cart holds a low stock and a sold out item, across two farms.
        $this->order($ananya, $farms['greenvalley'], 'CART',
            ['tomatoes' => 2, 'spinach' => 1, 'basil' => 1, 'strawberries' => 2], $this->now->subHour());

        foreach (['strawberries' => 2, 'spinach' => 20, 'basil' => 30, 'eggs' => 52, 'carrots' => 75] as $key => $hours) {
            $saved = $this->now->subHours($hours);
            $this->createAudited(Wishlist::class, [
                'customer_id' => $ananya->id,
                'product_id' => $this->products[$key]->id,
                'created_at' => $saved,
                'updated_at' => $saved,
            ]);
        }

        $followed = $this->now->subDays(12);
        $this->createAudited(FarmerFollow::class, [
            'customer_id' => $ananya->id,
            'farmer_id' => $farms['cedarcreek']->id,
            'created_at' => $followed,
            'updated_at' => $followed,
        ]);

        $ready = $this->order($ananya, $farms['greenvalley'], 'READY_FOR_PICKUP',
            ['tomatoes' => 2, 'spinach' => 1, 'carrots' => 1], $this->today(240));
        $confirmed = $this->order($ananya, $farms['cedarcreek'], 'CONFIRMED',
            ['apples' => 2, 'strawberries' => 1], $this->today(300));
        $this->order($ananya, $farms['hillside'], 'PENDING', ['milk' => 2, 'eggs' => 1], $this->today(180));
        $completed = $this->order($ananya, $farms['heritage'], 'COMPLETED',
            ['oats' => 1, 'lentils' => 2], $this->marketDayAgo('crawford', 7));
        $this->order($ananya, $farms['greenvalley'], 'COMPLETED',
            ['basil' => 2, 'tomatoes' => 1, 'carrots' => 2, 'spinach' => 1, 'salad' => 1], $this->marketDayAgo('colaba', 14));
        $cancelled = $this->order($ananya, $farms['cedarcreek'], 'CANCELLED',
            ['strawberries' => 2, 'apples' => 1], $this->marketDayAgo('crawford', 19));

        $this->notice($ananya, 'order_ready', 'Order ready for pickup', 'Your order is ready for pickup.',
            ['order_id' => (string) $ready->id], $this->now->subMinutes(40), false);
        $this->notice($ananya, 'order_confirmed', 'Order confirmed', 'The farmer has confirmed your order.',
            ['order_id' => (string) $confirmed->id], $confirmed->created_at->copy()->addHour(), true);
        $this->notice($ananya, 'order_completed', 'Order completed', 'Your order has been completed.',
            ['order_id' => (string) $completed->id], $completed->completed_at, true);
        $this->notice($ananya, 'order_cancelled', 'Order cancelled', 'The farmer cancelled the order.',
            ['order_id' => (string) $cancelled->id], $cancelled->created_at->copy()->addHours(2), true);
    }

    /**
     * Each farm's orders: seven for today's market (three new, four confirmed),
     * and six to eight picked up on every market day since five months ago.
     */
    private function farmOrders(array $customers, User $rajesh, array $farms): void
    {
        $shoppers = ['arjun', 'kavya', 'siddharth', 'nisha', 'farhan'];
        $turn = 0;
        foreach (['greenvalley', 'cedarcreek', 'hillside', 'heritage'] as $farmKey) {
            $farm = $farms[$farmKey];
            $own = array_keys(array_filter(self::PRODUCTS, fn ($product) => $product[0] === $farmKey));
            $inStock = array_values(array_filter($own, fn ($key) => $this->products[$key]->stock_qty > 0));

            foreach ([['PENDING', 25], ['PENDING', 120], ['PENDING', 300], ['CONFIRMED', 360],
                ['CONFIRMED', 420], ['CONFIRMED', 480], ['CONFIRMED', 540]] as [$status, $minutesAgo]) {
                $order = $this->order($customers[$shoppers[$turn++ % 5]], $farm, $status,
                    $this->pick($inStock), $this->today($minutesAgo));
                if ($farmKey === 'greenvalley' && $status === 'PENDING') {
                    $this->notice($rajesh, 'order_pending', 'New order',
                        'You have a new order that needs confirmation.',
                        ['order_id' => (string) $order->id], $order->created_at, false);
                }
            }

            $market = self::FARMS[$farmKey][2];
            [$opens, $closes] = $this->hours($market);
            $lastCancelled = null;
            foreach ($this->pastMarketDays($market) as $index => $day) {
                $count = mt_rand(6, 8);
                for ($n = 0; $n < $count; $n++) {
                    // Every other market day, one order was cancelled.
                    $status = $index % 2 === 1 && $n === 0 ? 'CANCELLED' : 'COMPLETED';
                    $order = $this->order($customers[$shoppers[$turn++ % 5]], $farm, $status,
                        $this->pick($own), $day->setTime(mt_rand($opens, $closes - 1), mt_rand(0, 59)));
                    $lastCancelled ??= $status === 'CANCELLED' ? $order : null;
                }
            }

            if ($farmKey === 'greenvalley' && $lastCancelled) {
                $this->notice($rajesh, 'order_cancelled', 'Order cancelled', 'The customer cancelled the order.',
                    ['order_id' => (string) $lastCancelled->id], $lastCancelled->created_at->copy()->addHours(6), true);
            }
        }

        foreach ([['spinach', 'stock_low', 60, false], ['peppers', 'stock_out', 1620, true], ['basil', 'stock_out', 3180, true]]
            as [$key, $type, $minutesAgo, $read]) {
            $product = $this->products[$key];
            $low = $type === 'stock_low';
            $this->notice($rajesh, $type, $low ? 'Low stock' : 'Out of stock',
                $low ? "{$product->name} has {$product->stock_qty} left in stock." : "{$product->name} is out of stock.",
                ['product_id' => (string) $product->id], $this->now->subMinutes($minutesAgo), $read);
        }
    }

    /** @param array<string, int> $items product key => quantity */
    private function order(User $customer, Farmer $farm, string $status, array $items, CarbonImmutable $at): Order
    {
        $lines = [];
        $total = 0;
        foreach ($items as $key => $quantity) {
            $product = $this->products[$key];
            $lineTotal = $product->price * $quantity;
            $total += $lineTotal;
            $lines[] = [$product, $quantity, $lineTotal];
        }

        // The server keeps no pickup time, so the app reads the time placed as
        // the pickup time; past orders are placed at the market for that reason.
        $order = $this->createAudited(Order::class, [
            'customer_id' => $customer->id,
            'farmer_id' => $farm->id,
            'delivery_address' => $customer->address,
            'city' => $customer->city,
            'district' => $customer->district,
            'ward' => $customer->ward,
            'status' => $status,
            'payment_method' => 'COD',
            'total_price' => $total,
            'completed_at' => $status === 'COMPLETED' ? $at : null,
            'created_at' => $at,
            'updated_at' => $at,
        ]);

        foreach ($lines as [$product, $quantity, $lineTotal]) {
            $this->createAudited(OrderItem::class, [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }

        return $order;
    }

    private function notice(User $user, string $type, string $title, string $body, array $data, \DateTimeInterface $at, bool $read): void
    {
        UserNotification::unguarded(fn () => UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'read_at' => $read ? $at : null,
            'created_at' => $at,
            'updated_at' => $at,
        ]));
    }

    /**
     * One to three of [$keys], one to three of each, never more than is in stock.
     *
     * @return array<string, int>
     */
    private function pick(array $keys): array
    {
        shuffle($keys);
        $items = [];
        foreach (array_slice($keys, 0, mt_rand(1, min(3, count($keys)))) as $key) {
            $stock = $this->products[$key]->stock_qty;
            $items[$key] = mt_rand(1, $stock > 0 ? min(3, $stock) : 3);
        }

        return $items;
    }

    /** A time earlier today, [$minutes] ago, and never before today began. */
    private function today(int $minutes): CarbonImmutable
    {
        $at = $this->now->subMinutes($minutes);
        $start = $this->now->startOfDay()->addMinutes(5);

        return $at->gt($start) ? $at : $start;
    }

    /** @return array{int, int} opening and closing hours */
    private function hours(string $market): array
    {
        preg_match('/(\d{2}):\d{2} - (\d{2}):\d{2}/', self::MARKETS[$market][6], $match);

        return [(int) $match[1], (int) $match[2]];
    }

    /** @return list<CarbonImmutable> the market's closed days since five months ago, newest first */
    private function pastMarketDays(string $market): array
    {
        [, $closes] = $this->hours($market);
        $days = [];
        $day = $this->now->startOfMonth()->subMonths(5)->startOfDay();
        while ($day->lte($this->now)) {
            if (in_array($day->dayOfWeekIso, self::MARKETS[$market][7], true) && $day->setTime($closes, 0)->lte($this->now)) {
                $days[] = $day;
            }
            $day = $day->addDay();
        }

        return array_reverse($days);
    }

    /** An hour after opening on the market's last day at least [$days] ago. */
    private function marketDayAgo(string $market, int $days): CarbonImmutable
    {
        [$opens] = $this->hours($market);
        foreach ($this->pastMarketDays($market) as $day) {
            if ($day->lte($this->now->subDays($days)->startOfDay())) {
                return $day->setTime($opens + 1, 0);
            }
        }

        return $this->now->subDays($days)->setTime($opens + 1, 0);
    }
}
