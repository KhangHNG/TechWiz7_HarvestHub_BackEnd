<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Services\CloudinaryService;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('farmer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('stock_qty')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('image_url')
                    ->label('Ảnh sản phẩm')
                    ->image()
                    ->multiple()
                    ->minFiles(1)
                    ->maxFiles(10)
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])
                    ->fetchFileInformation(false)
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        return app(CloudinaryService::class)->upload($file);
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
