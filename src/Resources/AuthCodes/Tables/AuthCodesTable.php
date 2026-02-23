<?php

namespace Webteractive\FilamentPassport\Resources\AuthCodes\Tables;

use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AuthCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('filament-passport::filament-passport.auth_code.columns.id'))
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->id)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label(__('filament-passport::filament-passport.auth_code.columns.user'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label(__('filament-passport::filament-passport.auth_code.columns.client'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('revoked')
                    ->label(__('filament-passport::filament-passport.auth_code.columns.revoked'))
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('filament-passport::filament-passport.auth_code.columns.expires_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('revoked')
                    ->label(__('filament-passport::filament-passport.auth_code.filters.revoked')),
            ])
            ->actions([
                Actions\Action::make('revoke')
                    ->label(__('filament-passport::filament-passport.auth_code.actions.revoke'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->forceFill(['revoked' => true])->save();

                        Notification::make()
                            ->title(__('filament-passport::filament-passport.auth_code.notifications.revoked'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => ! $record->revoked),
            ]);
    }
}
