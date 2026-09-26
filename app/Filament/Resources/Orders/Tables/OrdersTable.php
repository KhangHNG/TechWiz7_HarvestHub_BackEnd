<?php

namespace App\Filament\Resources\Orders\Tables;

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

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.full_name')
                    ->label('Khách hàng')
                    ->searchable(),
                TextColumn::make('farmer.business_name')
                    ->label('Nông dân')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->searchable(),
                TextColumn::make('total_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_by')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'CART' => 'Giỏ hàng',
                        'PENDING' => 'Chờ xác nhận',
                        'CONFIRMED' => 'Đã xác nhận',
                        'READY_FOR_PICKUP' => 'Sẵn sàng lấy hàng',
                        'COMPLETED' => 'Hoàn thành',
                        'CANCELLED' => 'Đã hủy',
                    ]),
                SelectFilter::make('customer')
                    ->label('Khách hàng')
                    ->relationship(
                        'customer',
                        'full_name',
                        fn (Builder $query): Builder => $query->where('role', 'CUSTOMER'),
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('farmer')
                    ->label('Nông dân')
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
