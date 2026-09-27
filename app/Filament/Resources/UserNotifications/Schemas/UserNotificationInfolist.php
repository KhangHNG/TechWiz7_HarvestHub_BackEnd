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
                TextEntry::make('user.full_name')->label('Recipient'),
                TextEntry::make('type')->label('Type'),
                TextEntry::make('title')->label('Title'),
                TextEntry::make('body')
                    ->label('Body')
                    ->columnSpanFull(),
                TextEntry::make('read_at')
                    ->label('Read at')
                    ->dateTime()
                    ->placeholder('Unread'),
                TextEntry::make('created_at')
                    ->label('Created at')
                    ->dateTime(),
            ]);
    }
}
