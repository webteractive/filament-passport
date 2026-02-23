<?php

namespace Webteractive\FilamentPassport\Resources\AccessTokens;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Laravel\Passport\Passport;
use Filament\Resources\Resource;
use Webteractive\FilamentPassport\Resources\AccessTokens\Tables\AccessTokensTable;
use Webteractive\FilamentPassport\Resources\AccessTokens\Schemas\AccessTokenInfolist;

class AccessTokenResource extends Resource
{
    public static function getModel(): string
    {
        return Passport::tokenModel();
    }

    public static function getModelLabel(): string
    {
        return config('filament-passport.resources.access_token.label', 'Access Token');
    }

    public static function getPluralModelLabel(): string
    {
        return config('filament-passport.resources.access_token.plural_label', 'Access Tokens');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-passport.navigation.group', 'OAuth');
    }

    public static function getNavigationSort(): ?int
    {
        $sort = config('filament-passport.navigation.sort');

        return $sort ? $sort + 1 : null;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-ticket';
    }

    public static function canAccess(): bool
    {
        return filament('filament-passport')->authorized();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccessTokenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccessTokensTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessTokens::route('/'),
            'view' => Pages\ViewAccessToken::route('/{record}'),
        ];
    }
}
