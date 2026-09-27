<?php

namespace App\Services;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\FarmerFollows\FarmerFollowResource;
use App\Filament\Resources\Farmers\FarmerResource;
use App\Filament\Resources\Markets\MarketResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Wishlists\WishlistResource;
use App\Models\Category;
use App\Models\Farmer;
use App\Models\FarmerFollow;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class AdminNotifier
{
    /**
     * @var array<class-string<Model>, class-string<Resource>>
     */
    private const RESOURCES = [
        User::class => UserResource::class,
        Farmer::class => FarmerResource::class,
        Product::class => ProductResource::class,
        Order::class => OrderResource::class,
        Market::class => MarketResource::class,
        Category::class => CategoryResource::class,
        Wishlist::class => WishlistResource::class,
        FarmerFollow::class => FarmerFollowResource::class,
    ];

    public function changed(Model $model, string $event): void
    {
        $admins = User::query()->where('role', 'ADMIN')->get();

        if ($admins->isEmpty()) {
            return;
        }

        $label = $this->label($model);
        $title = match ($event) {
            'created' => $label.' was just created',
            'updated' => $label.' was just updated',
            'deleted' => $label.' was just deleted',
            'restored' => $label.' was just restored',
            default => $label.' just changed',
        };
        $body = $this->body($model, $event);
        $url = $this->url($model, $event);

        foreach ($admins as $admin) {
            $notification = Notification::make()
                ->title($title)
                ->icon(match ($event) {
                    'created', 'restored' => Heroicon::OutlinedPlusCircle,
                    'deleted' => Heroicon::OutlinedTrash,
                    default => Heroicon::OutlinedPencilSquare,
                })
                ->status(match ($event) {
                    'created', 'restored' => 'success',
                    'deleted' => 'danger',
                    default => 'info',
                });

            if ($body) {
                $notification->body($body);
            }

            if ($url) {
                $notification->actions([
                    Action::make('open')
                        ->label('View')
                        ->url($url),
                ]);
            }

            $admin->notifyNow($notification->toDatabase());
        }
    }

    private function label(Model $model): string
    {
        return match (true) {
            $model instanceof User => 'User '.($model->full_name ?: '#'.$model->getKey()),
            $model instanceof Farmer => 'Farmer '.($model->business_name ?: '#'.$model->getKey()),
            $model instanceof Product => 'Product '.($model->name ?: '#'.$model->getKey()),
            $model instanceof Order => 'Order #'.$model->getKey(),
            $model instanceof Market => 'Market '.($model->name ?: '#'.$model->getKey()),
            $model instanceof Category => 'Category '.($model->name ?: '#'.$model->getKey()),
            $model instanceof Wishlist => 'Wishlist #'.$model->getKey(),
            $model instanceof FarmerFollow => 'Follow #'.$model->getKey(),
            default => class_basename($model).' #'.$model->getKey(),
        };
    }

    private function body(Model $model, string $event): ?string
    {
        if ($event !== 'updated') {
            return null;
        }

        if ($model instanceof Order && $model->wasChanged('status')) {
            return 'Status: '.$model->status;
        }

        if ($model instanceof Product && $model->wasChanged('stock_qty')) {
            return 'Stock: '.$model->stock_qty;
        }

        return null;
    }

    private function url(Model $model, string $event): ?string
    {
        $resource = self::RESOURCES[$model::class] ?? null;

        if (! $resource) {
            return null;
        }

        try {
            if ($event === 'deleted') {
                return $resource::getUrl('index', panel: 'admin');
            }

            $page = in_array($model::class, [User::class, Farmer::class], true) ? 'edit' : 'view';

            return $resource::getUrl($page, ['record' => $model], panel: 'admin');
        } catch (Throwable) {
            return null;
        }
    }
}
