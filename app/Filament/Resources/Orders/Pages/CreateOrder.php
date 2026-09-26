<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Services\OrderService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $status = $data['status'] ?? 'CART';
        $data['status'] = 'CART';
        $service = app(OrderService::class);
        $order = $service->createOrder(OrderForm::serviceData($data, true));

        if ($status !== 'PENDING') {
            return $order;
        }

        try {
            return $service->updateOrder($order, ['status' => 'PENDING']);
        } catch (InsufficientStockException $e) {
            $service->deleteOrder($order);
            Notification::make()->title($e->getMessage())->danger()->send();
            $this->halt();

            return $order;
        }
    }
}
