<?php

namespace Webteractive\FilamentPassport\Resources\AccessTokens\Tables;

use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Webteractive\FilamentPassport\Support\PassportClientManager;

class AccessTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('filament-passport::filament-passport.access_token.columns.id'))
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->id)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label(__('filament-passport::filament-passport.access_token.columns.user'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label(__('filament-passport::filament-passport.access_token.columns.client'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-passport::filament-passport.access_token.columns.name'))
                    ->placeholder('—')
                    ->searchable(),

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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('filament-passport::filament-passport.access_token.columns.expires_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('revoked')
                    ->label(__('filament-passport::filament-passport.access_token.filters.revoked')),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label(__('filament-passport::filament-passport.access_token.filters.client'))
                    ->relationship('client', 'name'),

                Tables\Filters\Filter::make('expired')
                    ->label(__('filament-passport::filament-passport.access_token.filters.expired'))
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now())),
            ])
            ->actions([
                Actions\ViewAction::make(),

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
            ])
            ->bulkActions([
                Actions\BulkAction::make('revoke')
                    ->label(__('filament-passport::filament-passport.access_token.actions.bulk_revoke'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $manager = app(PassportClientManager::class);

                        $records->each(fn ($record) => $manager->revokeToken($record));

                        Notification::make()
                            ->title(__('filament-passport::filament-passport.access_token.notifications.bulk_revoked'))
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
