<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Services\CloudinaryService;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('password_hash')
                    ->password()
                    ->required(),
                TextInput::make('address')
                    ->default(null),
                TextInput::make('city')
                    ->label('Thành phố')
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => $get('role') !== 'ADMIN'),
                TextInput::make('district')
                    ->label('Quận / huyện')
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => $get('role') !== 'ADMIN'),
                TextInput::make('capital')
                    ->label('Tỉnh / thành')
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => $get('role') !== 'ADMIN'),
                FileUpload::make('avatar_url')
                    ->label('Ảnh đại diện')
                    ->image()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])
                    ->fetchFileInformation(false)
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        return app(CloudinaryService::class)->upload($file, 'avatars');
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
                TextInput::make('role')
                    ->required()
                    ->default('CUSTOMER'),
                DateTimePicker::make('email_verified_at')
                    ->label('Email đã xác thực lúc'),
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
