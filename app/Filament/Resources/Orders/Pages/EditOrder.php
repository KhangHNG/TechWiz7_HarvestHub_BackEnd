<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Services\OrderService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->getRecord()->items()->get()->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ])->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return app(OrderService::class)->updateOrder(
                $record,
                OrderForm::serviceData($data, $record->status === 'CART'),
            );
        } catch (InsufficientStockException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            $this->halt();

            return $record;
        }
    }
}
