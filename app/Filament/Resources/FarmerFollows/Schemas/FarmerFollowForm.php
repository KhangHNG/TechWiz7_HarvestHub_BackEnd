<?php

namespace App\Filament\Resources\FarmerFollows\Schemas;

use App\Models\FarmerFollow;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FarmerFollowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship(
                        'customer',
                        'full_name',
                        fn (Builder $query) => $query->where('role', 'CUSTOMER'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('farmer_id')
                    ->label('Nông dân')
                    ->relationship('farmer', 'business_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules([
                        fn (Get $get, ?FarmerFollow $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                            $exists = FarmerFollow::query()
                                ->where('customer_id', $get('customer_id'))
                                ->where('farmer_id', $value)
                                ->when($record, fn (Builder $query) => $query->whereKeyNot($record->id))
                                ->exists();

                            if ($exists) {
                                $fail('Khách đã theo dõi nông dân này.');
                            }
                        },
                    ]),
            ]);
    }
}
