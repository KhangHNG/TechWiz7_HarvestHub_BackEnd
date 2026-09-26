<?php

namespace App\Filament\Tables\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class CreatedBetweenFilter
{
    public static function make(string $column = 'created_at', string $label = 'Ngày tạo'): Filter
    {
        return Filter::make($column)
            ->label($label)
            ->schema([
                DatePicker::make('from')->label('Từ ngày'),
                DatePicker::make('until')->label('Đến ngày'),
            ])
            ->query(function (Builder $query, array $data) use ($column): Builder {
                return $query
                    ->when(
                        $data['from'] ?? null,
                        fn (Builder $query, string $date): Builder => $query->whereDate($column, '>=', $date),
                    )
                    ->when(
                        $data['until'] ?? null,
                        fn (Builder $query, string $date): Builder => $query->whereDate($column, '<=', $date),
                    );
            })
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if ($from = $data['from'] ?? null) {
                    $indicators[] = Indicator::make($label.' từ '.$from);
                }

                if ($until = $data['until'] ?? null) {
                    $indicators[] = Indicator::make($label.' đến '.$until);
                }

                return $indicators;
            });
    }
}
