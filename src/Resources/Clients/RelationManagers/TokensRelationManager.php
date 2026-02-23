<?php

namespace Webteractive\FilamentPassport\Resources\Clients\RelationManagers;

use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Webteractive\FilamentPassport\Support\PassportClientManager;

class TokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    protected static ?string $title = null;

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('filament-passport::filament-passport.access_token.plural_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('filament-passport::filament-passport.access_token.columns.id'))
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->id),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-passport::filament-passport.access_token.columns.name'))
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('scopes')
                    ->label(__('filament-passport::filament-passport.access_token.columns.scopes'))
                    ->badge()
                    ->separator(','),

                Tables\Columns\IconColumn::make('revoked')
                    ->label(__('filament-passport::filament-passport.access_token.columns.revoked'))
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-passport::filament-passport.access_token.columns.created_at'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('filament-passport::filament-passport.access_token.columns.expires_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('revoke')
                    ->label(__('filament-passport::filament-passport.access_token.actions.revoke'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(PassportClientManager::class)->revokeToken($record);

                        Notification::make()
                            ->title(__('filament-passport::filament-passport.access_token.notifications.revoked'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => ! $record->revoked),
            ]);
    }
}
