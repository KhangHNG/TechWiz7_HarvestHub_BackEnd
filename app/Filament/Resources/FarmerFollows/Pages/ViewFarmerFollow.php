<?php

namespace App\Filament\Resources\FarmerFollows\Pages;

use App\Filament\Resources\FarmerFollows\FarmerFollowResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFarmerFollow extends ViewRecord
{
    protected static string $resource = FarmerFollowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
