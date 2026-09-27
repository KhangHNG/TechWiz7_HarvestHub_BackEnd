<?php

namespace App\Filament\Resources\FarmerFollows\Tables;

use App\Filament\Tables\Filters\CreatedBetweenFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FarmerFollowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('farmer.business_name')
                    ->label('Farmer')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer')
                    ->label('Customer')
                    ->relationship(
                        'customer',
                        'full_name',
                        fn (Builder $query): Builder => $query->where('role', 'CUSTOMER'),
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('farmer')
                    ->label('Farmer')
                    ->relationship('farmer', 'business_name')
                    ->searchable()
                    ->preload(),
                CreatedBetweenFilter::make(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
