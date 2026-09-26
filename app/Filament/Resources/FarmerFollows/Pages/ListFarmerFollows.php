<?php

namespace App\Filament\Resources\FarmerFollows\Pages;

use App\Filament\Resources\FarmerFollows\FarmerFollowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFarmerFollows extends ListRecords
{
    protected static string $resource = FarmerFollowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
