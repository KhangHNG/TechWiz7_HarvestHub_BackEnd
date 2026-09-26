<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
