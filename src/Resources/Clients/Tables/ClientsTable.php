<?php

namespace Webteractive\FilamentPassport\Resources\Clients\Tables;

use Filament\Tables;
use Filament\Actions;
use Filament\Infolists;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Webteractive\FilamentPassport\Support\PassportClientManager;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('filament-passport::filament-passport.client.columns.id'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-passport::filament-passport.client.columns.name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('provider')
                    ->label(__('filament-passport::filament-passport.client.columns.provider'))
                    ->badge()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('grant_type')
                    ->label(__('filament-passport::filament-passport.client.columns.grant_type'))
                    ->badge()
                    ->getStateUsing(fn (Model $record): string => ClientResource::summarizeGrantTypes($record)),

                Tables\Columns\TextColumn::make('redirect_uris')
                    ->label(__('filament-passport::filament-passport.client.columns.redirect_uris'))
                    ->getStateUsing(fn (Model $record): string => implode(', ', ClientResource::getRedirectUris($record)))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('confidential')
                    ->label(__('filament-passport::filament-passport.client.columns.confidential'))
                    ->getStateUsing(fn (Model $record): bool => ClientResource::isConfidential($record))
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('info'),

                Tables\Columns\IconColumn::make('revoked')
                    ->label(__('filament-passport::filament-passport.client.columns.revoked'))
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-passport::filament-passport.client.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('revoked')
                    ->label(__('filament-passport::filament-passport.client.filters.revoked')),

                Tables\Filters\SelectFilter::make('grant_type')
                    ->label(__('filament-passport::filament-passport.client.filters.grant_type'))
                    ->options(ClientResource::getGrantProfileOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $selectedGrantProfile = $data['value'] ?? null;

                        return ClientResource::applyGrantProfileFilter($query, $selectedGrantProfile);
                    }),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->schema([
                        Section::make(__('filament-passport::filament-passport.client.sections.details'))
                            ->schema([
                                Infolists\Components\TextEntry::make('id')
                                    ->label(__('filament-passport::filament-passport.client.columns.id'))
                                    ->state(fn (Model $record): string => (string) $record->getKey())
                                    ->copyable()
                                    ->copyableState(fn (Model $record): string => (string) $record->getKey())
                                    ->copyMessage('Client ID copied')
                                    ->copyMessageDuration(1500),

                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('filament-passport::filament-passport.client.columns.name'))
                                    ->state(fn (Model $record): string => (string) $record->name),

                                Infolists\Components\TextEntry::make('provider')
                                    ->label(__('filament-passport::filament-passport.client.columns.provider'))
                                    ->state(fn (Model $record): ?string => $record->provider)
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('grant_type')
                                    ->label(__('filament-passport::filament-passport.client.columns.grant_type'))
                                    ->state(fn (Model $record): string => ClientResource::summarizeGrantTypes($record))
                                    ->badge(),

                                Infolists\Components\TextEntry::make('client_secret')
                                    ->label(__('filament-passport::filament-passport.client.notifications.client_secret'))
                                    ->state(fn (Model $record): string => ClientResource::resolveDisplayableClientSecret($record))
                                    ->copyable(fn (Model $record): bool => ClientResource::resolveCopyableClientSecret($record) !== null)
                                    ->copyableState(fn (Model $record): ?string => ClientResource::resolveCopyableClientSecret($record))
                                    ->copyMessage('Client secret copied')
                                    ->copyMessageDuration(1500),

                                Infolists\Components\TextEntry::make('redirect_uris')
                                    ->label(__('filament-passport::filament-passport.client.columns.redirect_uris'))
                                    ->state(fn (Model $record): array => ClientResource::getRedirectUris($record))
                                    ->listWithLineBreaks()
                                    ->copyable(fn (Model $record): bool => ClientResource::getRedirectUris($record) !== [])
                                    ->copyableState(fn (Model $record): string => implode(PHP_EOL, ClientResource::getRedirectUris($record)))
                                    ->copyMessage('Redirect URI(s) copied')
                                    ->copyMessageDuration(1500)
                                    ->placeholder('—'),

                                Infolists\Components\IconEntry::make('confidential')
                                    ->label(__('filament-passport::filament-passport.client.columns.confidential'))
                                    ->state(fn (Model $record): bool => ClientResource::isConfidential($record))
                                    ->boolean()
                                    ->trueIcon('heroicon-o-lock-closed')
                                    ->falseIcon('heroicon-o-lock-open')
                                    ->trueColor('warning')
                                    ->falseColor('info'),

                                Infolists\Components\IconEntry::make('revoked')
                                    ->label(__('filament-passport::filament-passport.client.columns.revoked'))
                                    ->state(fn (Model $record): bool => (bool) $record->revoked)
                                    ->boolean()
                                    ->trueIcon('heroicon-o-x-circle')
                                    ->falseIcon('heroicon-o-check-circle')
                                    ->trueColor('danger')
                                    ->falseColor('success'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('filament-passport::filament-passport.client.columns.created_at'))
                                    ->state(fn (Model $record): mixed => $record->created_at)
                                    ->dateTime(),
                            ])
                            ->columns(2),
                    ]),

                Actions\EditAction::make(),

                Actions\Action::make('regenerate_secret')
                    ->label(__('filament-passport::filament-passport.client.actions.regenerate_secret'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament-passport::filament-passport.client.actions.regenerate_secret'))
                    ->modalDescription(__('filament-passport::filament-passport.client.actions.regenerate_secret_confirmation'))
                    ->action(function (Model $record): void {
                        $plainSecret = app(PassportClientManager::class)->regenerateSecret($record);

                        Notification::make()
                            ->title(__('filament-passport::filament-passport.client.notifications.secret_regenerated'))
                            ->body($plainSecret ?: __('filament-passport::filament-passport.client.notifications.secret_unavailable'))
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->visible(fn (?Model $record): bool => $record instanceof Model && ClientResource::isConfidential($record)),

                Actions\Action::make('revoke')
                    ->label(fn (?Model $record): string => ($record?->revoked ?? false)
                        ? __('filament-passport::filament-passport.client.actions.restore')
                        : __('filament-passport::filament-passport.client.actions.revoke')
                    )
                    ->icon(fn (?Model $record): string => ($record?->revoked ?? false)
                        ? 'heroicon-o-check-circle'
                        : 'heroicon-o-x-circle'
                    )
                    ->color(fn (?Model $record): string => ($record?->revoked ?? false) ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (Model $record): void {
                        $manager = app(PassportClientManager::class);
                        $wasRevoked = (bool) $record->revoked;

                        if ($wasRevoked) {
                            $manager->restoreClient($record);
                        } else {
                            $manager->revokeClient($record);
                        }

                        $message = $wasRevoked
                            ? __('filament-passport::filament-passport.client.notifications.restored')
                            : __('filament-passport::filament-passport.client.notifications.revoked');

                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Actions\BulkAction::make('revoke')
                    ->label(__('filament-passport::filament-passport.client.actions.bulk_revoke'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $manager = app(PassportClientManager::class);

                        $records->each(function (Model $record) use ($manager): void {
                            if (! $record->revoked) {
                                $manager->revokeClient($record);
                            }
                        });

                        Notification::make()
                            ->title(__('filament-passport::filament-passport.client.notifications.bulk_revoked'))
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
