<?php

namespace App\Filament\Resources\FarmerFollows\Pages;

use App\Filament\Resources\FarmerFollows\FarmerFollowResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFarmerFollow extends EditRecord
{
    protected static string $resource = FarmerFollowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
