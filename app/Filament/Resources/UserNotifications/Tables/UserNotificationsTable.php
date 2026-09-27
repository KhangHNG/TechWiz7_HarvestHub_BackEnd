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
                    ->label('Recipient')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('read_at')
                    ->label('Read at')
                    ->dateTime()
                    ->placeholder('Unread')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->label('Recipient')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'order_pending' => 'Pending confirmation',
                        'order_confirmed' => 'Confirmed',
                        'order_ready' => 'Ready for pickup',
                        'order_completed' => 'Completed',
                        'order_cancelled' => 'Cancelled',
                        'stock_low' => 'Low stock',
                        'stock_out' => 'Out of stock',
                    ]),
                TernaryFilter::make('read_at')
                    ->label('Read')
                    ->nullable()
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
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
