<?php

namespace App\Filament\Resources\Wishlists\Tables;

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

class WishlistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.full_name')
                    ->label('Khách hàng')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer')
                    ->label('Khách hàng')
                    ->relationship(
                        'customer',
                        'full_name',
                        fn (Builder $query): Builder => $query->where('role', 'CUSTOMER'),
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('product')
                    ->label('Sản phẩm')
                    ->relationship('product', 'name')
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
