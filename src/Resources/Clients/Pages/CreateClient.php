<?php

namespace Webteractive\FilamentPassport\Resources\Clients\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Webteractive\FilamentPassport\Support\PassportClientManager;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;
use Webteractive\FilamentPassport\Resources\Clients\Schemas\ClientForm;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected ?string $createdGrantProfile = null;

    protected ?string $createdClientId = null;

    protected ?string $createdClientSecret = null;

    protected ?bool $createdClientIsConfidential = null;

    /**
     * @var array<int, string>
     */
    protected array $createdRedirectUris = [];

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $manager = app(PassportClientManager::class);
        $grantProfile = $data['grant_profile'] ?? ClientResource::GrantAuthorizationCode;

        $result = $manager->createClient(
            name: (string) $data['name'],
            grantProfile: $grantProfile,
            redirectUris: ClientResource::parseRedirectUris($data['redirect_uris'] ?? []),
            provider: filled($data['provider'] ?? null) ? (string) $data['provider'] : null,
            confidential: (bool) ($data['confidential'] ?? true),
            enableDeviceFlow: (bool) ($data['enable_device_flow'] ?? false),
        );

        $this->createdGrantProfile = $result->grantProfile;
        $this->createdClientId = $result->clientId;
        $this->createdClientSecret = $result->clientSecret;
        $this->createdClientIsConfidential = $result->isConfidential;
        $this->createdRedirectUris = $result->redirectUris;

        return $result->client;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title(__('filament-passport::filament-passport.client.notifications.created'))
            ->body($this->buildNextStepsInstruction())
            ->success()
            ->persistent()
            ->send();
    }

    protected function buildNextStepsInstruction(): string
    {
        $header = [
            __('filament-passport::filament-passport.client.notifications.client_id').': '.$this->createdClientId,
            __('filament-passport::filament-passport.client.notifications.client_secret').': '.(
                $this->createdClientIsConfidential
                    ? ($this->createdClientSecret ?: __('filament-passport::filament-passport.client.notifications.secret_unavailable'))
                    : __('filament-passport::filament-passport.client.notifications.public_client')
            ),
            ...$this->buildRedirectUriDetails(),
            '',
            __('filament-passport::filament-passport.client.notifications.next_steps'),
        ];

        return implode(PHP_EOL, [
            ...$header,
            ...$this->resolveFlowInstructions(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function buildRedirectUriDetails(): array
    {
        if ($this->createdRedirectUris === []) {
            return [];
        }

        return [
            __('filament-passport::filament-passport.client.notifications.redirect_uris_label'),
            ...array_map(
                static fn (string $redirectUri): string => '- '.$redirectUri,
                $this->createdRedirectUris,
            ),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function resolveFlowInstructions(): array
    {
        return ClientForm::resolveUsageInstructions($this->createdGrantProfile ?? 'default');
    }
}
