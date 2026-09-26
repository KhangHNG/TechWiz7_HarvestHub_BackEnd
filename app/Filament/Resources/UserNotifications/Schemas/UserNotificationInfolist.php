<?php

namespace App\Filament\Resources\UserNotifications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserNotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.full_name')->label('Người nhận'),
                TextEntry::make('type')->label('Loại'),
                TextEntry::make('title')->label('Tiêu đề'),
                TextEntry::make('body')
                    ->label('Nội dung')
                    ->columnSpanFull(),
                TextEntry::make('read_at')
                    ->label('Lúc đọc')
                    ->dateTime()
                    ->placeholder('Chưa đọc'),
                TextEntry::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime(),
            ]);
    }
}
