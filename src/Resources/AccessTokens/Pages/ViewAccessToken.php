<?php

namespace Webteractive\FilamentPassport\Resources\AccessTokens\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Webteractive\FilamentPassport\Support\PassportClientManager;
use Webteractive\FilamentPassport\Resources\AccessTokens\AccessTokenResource;

class ViewAccessToken extends ViewRecord
{
    protected static string $resource = AccessTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('revoke')
                ->label(__('filament-passport::filament-passport.access_token.actions.revoke'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    app(PassportClientManager::class)->revokeToken($this->record);

                    Notification::make()
                        ->title(__('filament-passport::filament-passport.access_token.notifications.revoked'))
                        ->success()
                        ->send();

                    $this->redirect(AccessTokenResource::getUrl());
                })
                ->visible(fn () => ! $this->record->revoked),
        ];
    }
}
