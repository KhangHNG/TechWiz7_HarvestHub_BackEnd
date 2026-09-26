<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\FarmerFollow;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Observers\AdminChangeObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        $observer = AdminChangeObserver::class;

        User::observe($observer);
        Farmer::observe($observer);
        Product::observe($observer);
        Order::observe($observer);
        Market::observe($observer);
        Category::observe($observer);
        Wishlist::observe($observer);
        FarmerFollow::observe($observer);
    }
}
