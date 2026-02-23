<?php

namespace Webteractive\FilamentPassport\Support;

use RuntimeException;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Laravel\Passport\ClientRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;

class PassportClientManager
{
    protected const DeviceGrantType = 'urn:ietf:params:oauth:grant-type:device_code';

    /**
     * @param  array<int, string>  $redirectUris
     */
    public function createClient(
        string $name,
        string $grantProfile,
        array $redirectUris,
        ?string $provider,
        bool $confidential,
        bool $enableDeviceFlow,
    ): PassportClientCreationResult {
        [$grantTypes, $effectiveRedirectUris, $effectiveProvider, $effectiveConfidential] = match ($grantProfile) {
            ClientResource::GrantAuthorizationCode => [[
                'authorization_code',
                'refresh_token',
                ...($enableDeviceFlow ? [self::DeviceGrantType] : []),
            ], $redirectUris, null, $confidential],
            ClientResource::GrantAuthorizationCodePkce => [[
                'authorization_code',
                'refresh_token',
            ], $redirectUris, null, false],
            ClientResource::GrantDeviceAuthorization => [[
                self::DeviceGrantType,
                'refresh_token',
            ], [], null, $confidential],
            ClientResource::GrantPassword => [[
                'password',
                'refresh_token',
            ], [], $provider, $confidential],
            ClientResource::GrantImplicit => [[
                'implicit',
            ], $redirectUris, null, false],
            ClientResource::GrantClientCredentials => [[
                'client_credentials',
            ], [], null, true],
            default => throw new RuntimeException("Unsupported Passport grant profile [{$grantProfile}]."),
        };

        $record = $this->createClientRecord(
            name: $name,
            grantTypes: $grantTypes,
            redirectUris: $effectiveRedirectUris,
            provider: $effectiveProvider,
            confidential: $effectiveConfidential,
        );

        return new PassportClientCreationResult(
            client: $record,
            grantProfile: $grantProfile,
            clientId: (string) $record->getKey(),
            clientSecret: filled($record->plainSecret ?? null) ? (string) $record->plainSecret : null,
            isConfidential: method_exists($record, 'confidential')
                ? (bool) $record->confidential()
                : filled($record->secret ?? null),
            redirectUris: $redirectUris,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateClient(Model $record, array $data): Model
    {
        $redirectUris = ClientResource::parseRedirectUris($data['redirect_uris'] ?? []);
        $provider = filled($data['provider'] ?? null) ? (string) $data['provider'] : null;

        $updates = [
            'name' => (string) $data['name'],
        ];

        if ($this->hasColumn($record, 'redirect_uris')) {
            $updates['redirect_uris'] = $redirectUris;
        } elseif ($this->hasColumn($record, 'redirect')) {
            $updates['redirect'] = implode(',', $redirectUris);
        }

        if ($this->hasColumn($record, 'provider')) {
            $updates['provider'] = $provider;
        }

        $record->forceFill($updates)->save();

        return $record->refresh();
    }

    public function regenerateSecret(Model $record): ?string
    {
        app(ClientRepository::class)->regenerateSecret($record);

        $plainSecret = $record->plainSecret ?? null;

        return filled($plainSecret) ? (string) $plainSecret : null;
    }

    public function revokeClient(Model $record): void
    {
        $repository = app(ClientRepository::class);

        if (method_exists($repository, 'revoked')) {
            $repository->revoked($record->getKey());
        } else {
            $record->forceFill(['revoked' => true])->save();
        }

        $record->refresh();
    }

    public function restoreClient(Model $record): void
    {
        $record->forceFill(['revoked' => false])->save();
        $record->refresh();
    }

    public function revokeToken(Model $token): void
    {
        $token->revoke();

        if ($token->refreshToken) {
            $token->refreshToken->revoke();
        }
    }

    /**
     * @param  array<int, string>  $grantTypes
     * @param  array<int, string>  $redirectUris
     */
    protected function createClientRecord(
        string $name,
        array $grantTypes,
        array $redirectUris = [],
        ?string $provider = null,
        bool $confidential = true,
    ): Model {
        $client = Passport::client();

        $attributes = [
            'name' => $name,
            'secret' => $confidential ? Str::random(40) : null,
            'provider' => $provider,
            'revoked' => false,
            ...($this->hasColumn($client, 'redirect_uris') ? [
                'redirect_uris' => $redirectUris,
            ] : [
                'redirect' => implode(',', $redirectUris),
            ]),
            ...($this->hasColumn($client, 'grant_types') ? [
                'grant_types' => $grantTypes,
            ] : [
                'personal_access_client' => in_array('personal_access', $grantTypes, true),
                'password_client' => in_array('password', $grantTypes, true),
            ]),
        ];

        try {
            return $client->newQuery()->forceCreate($attributes);
        } catch (QueryException $exception) {
            if (
                array_key_exists($client->getKeyName(), $attributes) ||
                ! $this->isMissingPrimaryKeyDefaultException($exception, $client->getKeyName())
            ) {
                throw $exception;
            }

            if ($this->isNumericPrimaryKeyColumn($client)) {
                throw new RuntimeException(
                    "Unable to create Passport client: numeric primary key [{$client->getKeyName()}] has no default / auto-increment. ".
                    'Please fix oauth_clients schema or switch to UUID/string IDs.', previous: $exception);
            }

            $attributes[$client->getKeyName()] = $this->generateClientPrimaryKey($client);

            return $client->newQuery()->forceCreate($attributes);
        }
    }

    protected function generateClientPrimaryKey(Model $client): string
    {
        if (method_exists($client, 'newUniqueId')) {
            return (string) $client->newUniqueId();
        }

        return (string) Str::uuid();
    }

    protected function isMissingPrimaryKeyDefaultException(QueryException $exception, string $keyName): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, "field '{$keyName}' doesn't have a default value");
    }

    protected function isNumericPrimaryKeyColumn(Model $client): bool
    {
        $keyName = $client->getKeyName();

        if (! $this->hasColumn($client, $keyName)) {
            return $client->getKeyType() === 'int';
        }

        try {
            $columnType = strtolower((string) $client->getConnection()
                ->getSchemaBuilder()
                ->getColumnType($client->getTable(), $keyName));
        } catch (\Throwable) {
            return $client->getKeyType() === 'int';
        }

        return str_contains($columnType, 'int') || in_array($columnType, ['integer', 'bigint', 'smallint', 'mediumint', 'tinyint'], true);
    }

    protected function hasColumn(Model $record, string $column): bool
    {
        static $columnsByTable = [];

        $tableKey = implode('.', [
            $record->getConnectionName() ?: $record->getConnection()->getName(),
            $record->getTable(),
        ]);

        if (! array_key_exists($tableKey, $columnsByTable)) {
            $columnsByTable[$tableKey] = $record->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($record->getTable());
        }

        return in_array($column, $columnsByTable[$tableKey], true);
    }
}
