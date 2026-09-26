<?php

namespace App\Filament\Resources\FarmerFollows\Schemas;

use App\Models\FarmerFollow;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FarmerFollowInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.full_name')->label('Khách hàng'),
                TextEntry::make('farmer.business_name')->label('Nông dân'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (?FarmerFollow $record): bool => (bool) $record?->trashed()),
            ]);
    }
}
