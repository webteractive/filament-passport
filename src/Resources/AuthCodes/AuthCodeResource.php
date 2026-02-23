<?php

namespace Webteractive\FilamentPassport\Resources\AuthCodes;

use Filament\Tables\Table;
use Laravel\Passport\Passport;
use Filament\Resources\Resource;
use Webteractive\FilamentPassport\Resources\AuthCodes\Tables\AuthCodesTable;

class AuthCodeResource extends Resource
{
    public static function getModel(): string
    {
        return Passport::authCodeModel();
    }

    public static function getModelLabel(): string
    {
        return config('filament-passport.resources.auth_code.label', 'Auth Code');
    }

    public static function getPluralModelLabel(): string
    {
        return config('filament-passport.resources.auth_code.plural_label', 'Auth Codes');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-passport.navigation.group', 'OAuth');
    }

    public static function getNavigationSort(): ?int
    {
        $sort = config('filament-passport.navigation.sort');

        return $sort ? $sort + 2 : null;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-check';
    }

    public static function canAccess(): bool
    {
        return filament('filament-passport')->authorized();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return AuthCodesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthCodes::route('/'),
        ];
    }
}
