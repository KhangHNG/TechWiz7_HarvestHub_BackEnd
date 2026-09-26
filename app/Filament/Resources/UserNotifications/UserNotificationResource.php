<?php

namespace App\Filament\Resources\UserNotifications;

use App\Filament\Resources\UserNotifications\Pages\ListUserNotifications;
use App\Filament\Resources\UserNotifications\Pages\ViewUserNotification;
use App\Filament\Resources\UserNotifications\Schemas\UserNotificationInfolist;
use App\Filament\Resources\UserNotifications\Tables\UserNotificationsTable;
use App\Models\UserNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserNotificationResource extends Resource
{
    protected static ?string $model = UserNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?string $navigationLabel = 'Thông báo';

    protected static ?string $modelLabel = 'thông báo';

    protected static ?string $pluralModelLabel = 'thông báo';

    protected static ?string $recordTitleAttribute = 'title';

    public static function infolist(Schema $schema): Schema
    {
        return UserNotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserNotificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserNotifications::route('/'),
            'view' => ViewUserNotification::route('/{record}'),
        ];
    }
}
