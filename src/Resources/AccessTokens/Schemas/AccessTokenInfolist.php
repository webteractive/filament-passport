<?php

namespace Webteractive\FilamentPassport\Resources\AccessTokens\Schemas;

use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class AccessTokenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-passport::filament-passport.access_token.sections.details'))
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label(__('filament-passport::filament-passport.access_token.columns.id'))
                            ->copyable()
                            ->copyMessage('Token ID copied')
                            ->copyMessageDuration(1500),

                        Infolists\Components\TextEntry::make('name')
                            ->label(__('filament-passport::filament-passport.access_token.columns.name'))
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('client.name')
                            ->label(__('filament-passport::filament-passport.access_token.columns.client')),

                        Infolists\Components\TextEntry::make('scopes')
                            ->label(__('filament-passport::filament-passport.access_token.columns.scopes'))
                            ->badge(),

                        Infolists\Components\IconEntry::make('revoked')
                            ->label(__('filament-passport::filament-passport.access_token.columns.revoked'))
                            ->boolean()
                            ->trueIcon('heroicon-o-x-circle')
                            ->falseIcon('heroicon-o-check-circle')
                            ->trueColor('danger')
                            ->falseColor('success'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('filament-passport::filament-passport.access_token.columns.created_at'))
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('expires_at')
                            ->label(__('filament-passport::filament-passport.access_token.columns.expires_at'))
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
