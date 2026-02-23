<?php

namespace Webteractive\FilamentPassport\Resources\Clients\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;

class ClientForm
{
    /**
     * @return array<int, string>
     */
    public static function resolveUsageInstructions(string $grantProfile): array
    {
        $key = "filament-passport::filament-passport.client.usage_instructions.{$grantProfile}";
        $instructions = __($key);

        if (is_array($instructions)) {
            return array_map(
                static fn (int $index, string $step): string => ($index + 1).'. '.$step,
                array_keys($instructions),
                array_values($instructions),
            );
        }

        return [__('filament-passport::filament-passport.client.usage_instructions.default')];
    }

    /**
     * @return array<int, Forms\Components\TextInput>
     */
    public static function resolveEndpointFields(string $grantProfile, Model $record): array
    {
        $fields = [];

        $grantsWithAuthorizationUrl = [
            ClientResource::GrantAuthorizationCode,
            ClientResource::GrantAuthorizationCodePkce,
            ClientResource::GrantImplicit,
        ];

        if (in_array($grantProfile, $grantsWithAuthorizationUrl, true)) {
            $fields[] = Forms\Components\TextInput::make('authorization_url')
                ->label(__('filament-passport::filament-passport.client.endpoints.authorization_url'))
                ->afterStateHydrated(fn (Forms\Components\TextInput $component) => $component->state(url('/oauth/authorize')))
                ->readOnly()
                ->dehydrated(false)
                ->copyable(true, __('filament-passport::filament-passport.client.endpoints.copied'));
        }

        if ($grantProfile !== ClientResource::GrantImplicit) {
            $fields[] = Forms\Components\TextInput::make('token_url')
                ->label(__('filament-passport::filament-passport.client.endpoints.token_url'))
                ->afterStateHydrated(fn (Forms\Components\TextInput $component) => $component->state(url('/oauth/token')))
                ->readOnly()
                ->dehydrated(false)
                ->copyable(true, __('filament-passport::filament-passport.client.endpoints.copied'));
        }

        if ($grantProfile === ClientResource::GrantDeviceAuthorization) {
            $fields[] = Forms\Components\TextInput::make('device_code_url')
                ->label(__('filament-passport::filament-passport.client.endpoints.device_code_url'))
                ->afterStateHydrated(fn (Forms\Components\TextInput $component) => $component->state(url('/oauth/device/code')))
                ->readOnly()
                ->dehydrated(false)
                ->copyable(true, __('filament-passport::filament-passport.client.endpoints.copied'));
        }

        if (ClientResource::grantProfileRequiresRedirectUris($grantProfile)) {
            $redirectUris = ClientResource::getRedirectUris($record);

            foreach ($redirectUris as $index => $uri) {
                $label = __('filament-passport::filament-passport.client.endpoints.callback_url');
                $suffix = count($redirectUris) > 1 ? ' '.($index + 1) : '';

                $fields[] = Forms\Components\TextInput::make("callback_url_{$index}")
                    ->label($label.$suffix)
                    ->afterStateHydrated(fn (Forms\Components\TextInput $component) => $component->state($uri))
                    ->readOnly()
                    ->dehydrated(false)
                    ->copyable(true, __('filament-passport::filament-passport.client.endpoints.copied'));
            }
        }

        return $fields;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Section::make(__('filament-passport::filament-passport.client.sections.details'))
                    ->columnSpan(3)
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label(__('filament-passport::filament-passport.client.notifications.client_id'))
                            ->hiddenOn('create')
                            ->dehydrated(false)
                            ->readOnly()
                            ->copyable(true, 'Client ID copied')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, mixed $state, ?Model $record): void {
                                if (! $record instanceof Model) {
                                    return;
                                }

                                $keyName = $record->getKeyName();
                                $component->state((string) ($record->getRawOriginal($keyName) ?: $record->getKey()));
                            }),

                        Forms\Components\TextInput::make('name')
                            ->label(__('filament-passport::filament-passport.client.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('filament-passport::filament-passport.client.fields.name_help')),

                        Forms\Components\Select::make('grant_profile')
                            ->label(__('filament-passport::filament-passport.client.fields.grant_profile'))
                            ->options(ClientResource::getGrantProfileOptions())
                            ->required()
                            ->default(ClientResource::GrantAuthorizationCode)
                            ->live()
                            ->afterStateHydrated(function (Forms\Components\Select $component, mixed $state, ?Model $record): void {
                                if (filled($state) || ! $record) {
                                    return;
                                }

                                $component->state(ClientResource::inferGrantProfileFromRecord($record));
                            })
                            ->disabledOn('edit')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create')
                            ->helperText(__('filament-passport::filament-passport.client.fields.grant_profile_help')),

                        Forms\Components\Repeater::make('redirect_uris')
                            ->label(__('filament-passport::filament-passport.client.fields.redirect_uris'))
                            ->simple(
                                Forms\Components\TextInput::make('url')
                                    ->label(__('filament-passport::filament-passport.client.fields.redirect_uri'))
                                    ->placeholder(__('filament-passport::filament-passport.client.fields.redirect_uri_placeholder'))
                                    ->url()
                                    ->required()
                            )
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, mixed $state, ?Model $record): void {
                                $uris = [];

                                if ($record instanceof Model) {
                                    $uris = ClientResource::getRedirectUris($record);
                                } elseif (is_string($state) && filled($state)) {
                                    $uris = ClientResource::parseRedirectUris($state);
                                }

                                if ($uris === []) {
                                    return;
                                }

                                $simpleFieldName = $component->getSimpleField()?->getName();
                                $items = [];

                                foreach ($uris as $uri) {
                                    $itemData = $simpleFieldName ? [$simpleFieldName => $uri] : $uri;

                                    if ($uuid = $component->generateUuid()) {
                                        $items[$uuid] = $itemData;
                                    } else {
                                        $items[] = $itemData;
                                    }
                                }

                                $component->rawState($items);
                            })
                            ->required(fn (Get $get): bool => ClientResource::grantProfileRequiresRedirectUris($get('grant_profile')))
                            ->defaultItems(0)
                            ->addActionLabel(__('filament-passport::filament-passport.client.fields.add_redirect_uri'))
                            ->helperText(__('filament-passport::filament-passport.client.fields.redirect_uris_help')),

                        Forms\Components\Select::make('provider')
                            ->label(__('filament-passport::filament-passport.client.fields.provider'))
                            ->options(fn () => collect(config('auth.providers', []))
                                ->mapWithKeys(fn ($provider, $key) => [$key => ucfirst($key)])
                                ->toArray()
                            )
                            ->required(fn (Get $get): bool => $get('grant_profile') === ClientResource::GrantPassword)
                            ->nullable()
                            ->helperText(__('filament-passport::filament-passport.client.fields.provider_help')),

                        Forms\Components\Toggle::make('confidential')
                            ->label(__('filament-passport::filament-passport.client.fields.confidential'))
                            ->helperText(__('filament-passport::filament-passport.client.fields.confidential_help'))
                            ->default(true)
                            ->visible(fn (Get $get): bool => in_array($get('grant_profile'), array_filter([
                                ClientResource::GrantAuthorizationCode,
                                ClientResource::supportsDeviceFlow() ? ClientResource::GrantDeviceAuthorization : null,
                                ClientResource::GrantPassword,
                            ]), true))
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\Toggle::make('enable_device_flow')
                            ->label(__('filament-passport::filament-passport.client.fields.enable_device_flow'))
                            ->helperText(__('filament-passport::filament-passport.client.fields.enable_device_flow_help'))
                            ->default(false)
                            ->visible(fn (Get $get): bool => ClientResource::supportsDeviceFlow() && $get('grant_profile') === ClientResource::GrantAuthorizationCode)
                            ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                    ]),

                Section::make(__('filament-passport::filament-passport.client.sections.usage_instructions'))
                    ->columnSpan(2)
                    ->icon('heroicon-o-book-open')
                    ->collapsible()
                    ->hiddenOn('create')
                    ->schema(function (?Model $record): array {
                        if (! $record) {
                            return [];
                        }

                        $grantProfile = ClientResource::inferGrantProfileFromRecord($record);

                        $endpointFields = static::resolveEndpointFields($grantProfile, $record);

                        $instructions = static::resolveUsageInstructions($grantProfile);
                        $instructionFields = array_map(
                            static fn (string $step, int $index): Forms\Components\Placeholder => Forms\Components\Placeholder::make("usage_step_{$index}")
                                ->hiddenLabel()
                                ->content($step),
                            $instructions,
                            array_keys($instructions),
                        );

                        return [
                            Fieldset::make(__('filament-passport::filament-passport.client.sections.endpoints'))
                                ->schema($endpointFields)
                                ->columns(1),
                            ...$instructionFields,
                        ];
                    }),
            ]);
    }
}
