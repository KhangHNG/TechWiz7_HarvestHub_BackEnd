<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Services\CloudinaryService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                // PNG or WebP only: a cut-out needs its see-through background.
                FileUpload::make('image_url')
                    ->label('Category picture')
                    ->helperText('The food cut out on a see-through background, like the other category tiles.')
                    ->image()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/png', 'image/webp'])
                    ->fetchFileInformation(false)
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        return app(CloudinaryService::class)->upload($file, 'categories');
                    })
                    // Uploads are Cloudinary links; seeded pictures sit in the
                    // public folder.
                    ->getUploadedFileUsing(function (string $file): array {
                        return [
                            'name' => basename(parse_url($file, PHP_URL_PATH) ?: $file),
                            'size' => 0,
                            'type' => null,
                            'url' => str_starts_with($file, 'http') ? $file : url($file),
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
