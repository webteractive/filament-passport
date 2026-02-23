<?php

namespace Webteractive\FilamentPassport\Resources\Clients;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Laravel\Passport\Passport;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Webteractive\FilamentPassport\Resources\Clients\Schemas\ClientForm;
use Webteractive\FilamentPassport\Resources\Clients\Tables\ClientsTable;
use Webteractive\FilamentPassport\Resources\Clients\RelationManagers\TokensRelationManager;

class ClientResource extends Resource
{
    public const GrantAuthorizationCode = 'authorization_code';

    public const GrantAuthorizationCodePkce = 'authorization_code_pkce';

    public const GrantDeviceAuthorization = 'device_authorization';

    public const GrantPassword = 'password';

    public const GrantImplicit = 'implicit';

    public const GrantClientCredentials = 'client_credentials';

    public static function getModel(): string
    {
        return Passport::clientModel();
    }

    public static function getModelLabel(): string
    {
        return config('filament-passport.resources.client.label', 'Client');
    }

    public static function getPluralModelLabel(): string
    {
        return config('filament-passport.resources.client.plural_label', 'Clients');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-passport.navigation.group', 'OAuth');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-passport.navigation.sort');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-key';
    }

    public static function canAccess(): bool
    {
        return filament('filament-passport')->authorized();
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TokensRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function supportsDeviceFlow(): bool
    {
        return method_exists(Passport::class, 'deviceAuthorizationView');
    }

    public static function getGrantProfileOptions(): array
    {
        $options = [
            static::GrantAuthorizationCode => __('filament-passport::filament-passport.client.grant_profiles.authorization_code'),
            static::GrantAuthorizationCodePkce => __('filament-passport::filament-passport.client.grant_profiles.authorization_code_pkce'),
            static::GrantDeviceAuthorization => __('filament-passport::filament-passport.client.grant_profiles.device_authorization'),
            static::GrantPassword => __('filament-passport::filament-passport.client.grant_profiles.password'),
            static::GrantImplicit => __('filament-passport::filament-passport.client.grant_profiles.implicit'),
            static::GrantClientCredentials => __('filament-passport::filament-passport.client.grant_profiles.client_credentials'),
        ];

        if (! static::supportsDeviceFlow()) {
            unset($options[static::GrantDeviceAuthorization]);
        }

        return $options;
    }

    public static function grantProfileRequiresRedirectUris(?string $grantProfile): bool
    {
        return in_array($grantProfile, [
            static::GrantAuthorizationCode,
            static::GrantAuthorizationCodePkce,
            static::GrantImplicit,
        ], true);
    }

    public static function parseRedirectUris(array|string|null $redirectUris): array
    {
        if (is_array($redirectUris)) {
            return array_values(array_unique(array_filter(array_map(
                static fn (mixed $value): string => trim((string) $value),
                $redirectUris,
            ))));
        }

        if (! filled($redirectUris)) {
            return [];
        }

        $parts = preg_split('/[\r\n,]+/', $redirectUris) ?: [];

        return array_values(array_unique(array_filter(array_map(
            static fn (string $value): string => trim($value),
            $parts,
        ))));
    }

    public static function getRedirectUris(Model $record): array
    {
        if (static::hasClientColumn('redirect_uris')) {
            return static::parseRedirectUris($record->redirect_uris);
        }

        return static::parseRedirectUris($record->redirect ?? []);
    }

    public static function inferGrantProfileFromRecord(Model $record): string
    {
        $grantTypes = static::resolveGrantTypes($record);

        if (in_array('implicit', $grantTypes, true)) {
            return static::GrantImplicit;
        }

        if (in_array('password', $grantTypes, true)) {
            return static::GrantPassword;
        }

        if (in_array('client_credentials', $grantTypes, true)) {
            return static::GrantClientCredentials;
        }

        if (
            in_array(static::getDeviceGrantIdentifier(), $grantTypes, true) &&
            ! in_array('authorization_code', $grantTypes, true)
        ) {
            return static::GrantDeviceAuthorization;
        }

        if (in_array('authorization_code', $grantTypes, true) && ! static::isConfidential($record)) {
            return static::GrantAuthorizationCodePkce;
        }

        return static::GrantAuthorizationCode;
    }

    public static function summarizeGrantTypes(Model $record): string
    {
        $grantTypes = static::resolveGrantTypes($record);
        $grantProfile = static::inferGrantProfileFromRecord($record);
        $grantProfileLabel = static::getGrantProfileOptions()[$grantProfile] ?? null;

        if ($grantProfile === static::GrantAuthorizationCode) {
            if (in_array(static::getDeviceGrantIdentifier(), $grantTypes, true)) {
                return implode(', ', array_filter([
                    $grantProfileLabel,
                    static::grantTypeToLabel(static::getDeviceGrantIdentifier()),
                ]));
            }

            return $grantProfileLabel ?? static::grantTypeToLabel('authorization_code');
        }

        if (filled($grantProfileLabel)) {
            return $grantProfileLabel;
        }

        $labels = array_map(static fn (string $grantType): string => static::grantTypeToLabel($grantType), $grantTypes);

        return implode(', ', array_unique($labels));
    }

    public static function isConfidential(Model $record): bool
    {
        if (method_exists($record, 'confidential')) {
            return (bool) $record->confidential();
        }

        return filled($record->secret ?? null);
    }

    public static function applyGrantProfileFilter(Builder $query, ?string $selectedGrantProfile): Builder
    {
        if (! filled($selectedGrantProfile)) {
            return $query;
        }

        return match ($selectedGrantProfile) {
            static::GrantAuthorizationCode => $query
                ->where(fn (Builder $builder): Builder => static::queryByGrantType($builder, 'authorization_code'))
                ->whereNotNull('secret'),
            static::GrantAuthorizationCodePkce => $query
                ->where(fn (Builder $builder): Builder => static::queryByGrantType($builder, 'authorization_code'))
                ->whereNull('secret'),
            static::GrantDeviceAuthorization => $query
                ->where(fn (Builder $builder): Builder => static::queryByGrantType($builder, static::getDeviceGrantIdentifier()))
                ->where(fn (Builder $builder): Builder => static::queryByMissingGrantType($builder, 'authorization_code')),
            static::GrantPassword => $query->where(fn (Builder $builder): Builder => static::queryByGrantType($builder, 'password')),
            static::GrantImplicit => $query->where(fn (Builder $builder): Builder => static::queryByGrantType($builder, 'implicit')),
            static::GrantClientCredentials => $query->where(fn (Builder $builder): Builder => static::queryByGrantType($builder, 'client_credentials')),
            default => $query,
        };
    }

    public static function hasClientColumn(string $column): bool
    {
        static $availableColumns = null;

        if ($availableColumns === null) {
            $client = Passport::client();
            $availableColumns = $client->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($client->getTable());
        }

        return in_array($column, $availableColumns, true);
    }

    public static function resolveDisplayableClientSecret(Model $record): string
    {
        if (! static::isConfidential($record)) {
            return __('filament-passport::filament-passport.client.notifications.public_client');
        }

        $plainSecret = $record->plainSecret ?? null;

        if (filled($plainSecret)) {
            return (string) $plainSecret;
        }

        return __('filament-passport::filament-passport.client.notifications.secret_unavailable');
    }

    public static function resolveCopyableClientSecret(Model $record): ?string
    {
        if (! static::isConfidential($record)) {
            return null;
        }

        $plainSecret = $record->plainSecret ?? null;

        if (! filled($plainSecret)) {
            return null;
        }

        return (string) $plainSecret;
    }

    protected static function resolveGrantTypes(Model $record): array
    {
        if (static::hasClientColumn('grant_types') && is_array($record->grant_types ?? null)) {
            return array_values(array_unique($record->grant_types));
        }

        $grantTypes = [];

        if (($record->personal_access_client ?? false) === true) {
            $grantTypes[] = 'personal_access';
        }

        if (($record->password_client ?? false) === true) {
            $grantTypes[] = 'password';
            $grantTypes[] = 'refresh_token';
        }

        if (static::getRedirectUris($record) !== []) {
            $grantTypes[] = 'authorization_code';
            $grantTypes[] = 'refresh_token';
        }

        if (empty($grantTypes) && static::isConfidential($record)) {
            $grantTypes[] = 'client_credentials';
        }

        return array_values(array_unique($grantTypes));
    }

    protected static function grantTypeToLabel(string $grantType): string
    {
        return match ($grantType) {
            'authorization_code' => __('filament-passport::filament-passport.client.grant_types.authorization_code'),
            static::getDeviceGrantIdentifier() => __('filament-passport::filament-passport.client.grant_types.device_authorization'),
            'client_credentials' => __('filament-passport::filament-passport.client.grant_types.client_credentials'),
            'implicit' => __('filament-passport::filament-passport.client.grant_types.implicit'),
            'password' => __('filament-passport::filament-passport.client.grant_types.password'),
            'personal_access' => __('filament-passport::filament-passport.client.grant_types.personal_access'),
            'refresh_token' => __('filament-passport::filament-passport.client.grant_types.refresh_token'),
            default => $grantType,
        };
    }

    protected static function queryByGrantType(Builder $query, string $grantType): Builder
    {
        if (static::hasClientColumn('grant_types')) {
            return $query->where('grant_types', 'like', '%"'.$grantType.'"%');
        }

        return match ($grantType) {
            'password' => static::hasClientColumn('password_client')
                ? $query->where('password_client', true)
                : $query->whereRaw('1 = 0'),
            'authorization_code' => static::hasClientColumn('redirect')
                ? $query->whereNotNull('redirect')->where('redirect', '!=', '')
                : $query->whereRaw('1 = 0'),
            'implicit' => static::hasClientColumn('redirect')
                ? $query->whereNotNull('redirect')->where('redirect', '!=', '')->whereNull('secret')
                : $query->whereRaw('1 = 0'),
            'client_credentials' => (
                static::hasClientColumn('password_client') &&
                static::hasClientColumn('personal_access_client') &&
                static::hasClientColumn('redirect')
            )
                ? $query
                    ->where('password_client', false)
                    ->where('personal_access_client', false)
                    ->where(function (Builder $builder): Builder {
                        return $builder->whereNull('redirect')->orWhere('redirect', '');
                    })
                : $query->whereRaw('1 = 0'),
            default => $query->whereRaw('1 = 0'),
        };
    }

    protected static function queryByMissingGrantType(Builder $query, string $grantType): Builder
    {
        if (static::hasClientColumn('grant_types')) {
            return $query->where('grant_types', 'not like', '%"'.$grantType.'"%');
        }

        return $query;
    }

    protected static function getDeviceGrantIdentifier(): string
    {
        return 'urn:ietf:params:oauth:grant-type:device_code';
    }
}
