<?php

namespace App\Filament\Resources\UserNotifications\Tables;

use App\Filament\Tables\Filters\CreatedBetweenFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('Người nhận')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('read_at')
                    ->label('Lúc đọc')
                    ->dateTime()
                    ->placeholder('Chưa đọc')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->label('Người nhận')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Loại')
                    ->options([
                        'order_pending' => 'Chờ xác nhận',
                        'order_confirmed' => 'Đã xác nhận',
                        'order_ready' => 'Sẵn sàng lấy hàng',
                        'order_completed' => 'Hoàn thành',
                        'order_cancelled' => 'Đã hủy',
                        'stock_low' => 'Sắp hết hàng',
                        'stock_out' => 'Hết hàng',
                    ]),
                TernaryFilter::make('read_at')
                    ->label('Đã đọc')
                    ->nullable()
                    ->trueLabel('Đã đọc')
                    ->falseLabel('Chưa đọc'),
                CreatedBetweenFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
