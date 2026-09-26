<?php

namespace App\Filament\Resources\FarmerFollows;

use App\Filament\Resources\FarmerFollows\Pages\CreateFarmerFollow;
use App\Filament\Resources\FarmerFollows\Pages\EditFarmerFollow;
use App\Filament\Resources\FarmerFollows\Pages\ListFarmerFollows;
use App\Filament\Resources\FarmerFollows\Pages\ViewFarmerFollow;
use App\Filament\Resources\FarmerFollows\Schemas\FarmerFollowForm;
use App\Filament\Resources\FarmerFollows\Schemas\FarmerFollowInfolist;
use App\Filament\Resources\FarmerFollows\Tables\FarmerFollowsTable;
use App\Models\FarmerFollow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FarmerFollowResource extends Resource
{
    protected static ?string $model = FarmerFollow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Theo dõi';

    protected static ?string $modelLabel = 'theo dõi';

    protected static ?string $pluralModelLabel = 'theo dõi';

    public static function form(Schema $schema): Schema
    {
        return FarmerFollowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FarmerFollowInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FarmerFollowsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFarmerFollows::route('/'),
            'create' => CreateFarmerFollow::route('/create'),
            'view' => ViewFarmerFollow::route('/{record}'),
            'edit' => EditFarmerFollow::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
