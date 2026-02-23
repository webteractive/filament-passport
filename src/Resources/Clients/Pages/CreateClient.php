<?php

namespace Webteractive\FilamentPassport\Resources\Clients\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Webteractive\FilamentPassport\Support\PassportClientManager;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;

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
            'Redirect URI(s):',
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
        return match ($this->createdGrantProfile) {
            ClientResource::GrantAuthorizationCode => [
                '1. Redirect the user to /oauth/authorize with response_type=code, client_id, redirect_uri, scope, and state.',
                '2. Exchange the code at POST /oauth/token with grant_type=authorization_code, client_id, client_secret, redirect_uri, and code.',
            ],
            ClientResource::GrantAuthorizationCodePkce => [
                '1. PKCE clients are public: use client_id + redirect_uri (no client_secret).',
                '2. Generate code_verifier and code_challenge (S256) in your app for each authorization request.',
                '3. Redirect to /oauth/authorize with response_type=code, client_id, redirect_uri, state, code_challenge, and code_challenge_method=S256.',
                '4. Exchange at POST /oauth/token with grant_type=authorization_code, client_id, redirect_uri, code, and code_verifier.',
            ],
            ClientResource::GrantDeviceAuthorization => [
                '1. Request a device code via POST /oauth/device/code with client_id, scope, and client_secret if confidential.',
                '2. Show verification_uri and user_code to the user.',
                '3. Poll POST /oauth/token with grant_type=urn:ietf:params:oauth:grant-type:device_code, client_id, device_code, and client_secret if confidential.',
            ],
            ClientResource::GrantPassword => [
                '1. Enable the password grant in AppServiceProvider with Passport::enablePasswordGrant().',
                '2. Request tokens from POST /oauth/token with grant_type=password, client_id, username, password, scope, and client_secret if confidential.',
            ],
            ClientResource::GrantImplicit => [
                '1. Enable implicit grant in AppServiceProvider with Passport::enableImplicitGrant().',
                '2. Redirect to /oauth/authorize with response_type=token, client_id, redirect_uri, scope, and state.',
            ],
            ClientResource::GrantClientCredentials => [
                '1. Request a token from POST /oauth/token with grant_type=client_credentials, client_id, client_secret, and scope.',
                '2. Protect machine-to-machine routes with Passport client middleware.',
            ],
            default => [
                '1. Use /oauth/authorize to request authorization and /oauth/token to exchange for access tokens.',
            ],
        };
    }
}
