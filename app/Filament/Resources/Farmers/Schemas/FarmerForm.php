<?php

namespace App\Filament\Resources\Farmers\Schemas;

use App\Services\CloudinaryService;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FarmerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('market_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('business_name')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('rating')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_accepting_orders')
                    ->label('Accepting orders')
                    ->default(true),
                FileUpload::make('cover_url')
                    ->label('Cover image')
                    ->image()
                    ->maxSize(1536)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])
                    ->fetchFileInformation(false)
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        return app(CloudinaryService::class)->upload($file, 'covers');
                    })
                    ->getUploadedFileUsing(function (BaseFileUpload $component, string $file): array {
                        return [
                            'name' => basename(parse_url($file, PHP_URL_PATH) ?: $file),
                            'size' => 0,
                            'type' => null,
                            'url' => str_starts_with($file, 'http') ? $file : $component->getDisk()->url($file),
                        ];
                    })
                    ->deleteUploadedFileUsing(function (string $file): void {
                        app(CloudinaryService::class)->deleteByUrl($file);
                    }),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
                TextInput::make('updated_by')
                    ->numeric()
                    ->default(null),
                TextInput::make('deleted_by')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
