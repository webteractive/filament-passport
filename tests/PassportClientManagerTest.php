<?php

use Laravel\Passport\Passport;
use Webteractive\FilamentPassport\Support\PassportClientManager;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;
use Webteractive\FilamentPassport\Support\PassportClientCreationResult;

test('creates authorization code client', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Test Auth Code Client',
        grantProfile: ClientResource::GrantAuthorizationCode,
        redirectUris: ['https://example.com/callback'],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );

    expect($result)
        ->toBeInstanceOf(PassportClientCreationResult::class)
        ->and($result->grantProfile)->toBe(ClientResource::GrantAuthorizationCode)
        ->and($result->clientId)->not->toBeEmpty()
        ->and($result->isConfidential)->toBeTrue()
        ->and($result->redirectUris)->toBe(['https://example.com/callback']);

    $client = Passport::client()->find($result->clientId);
    expect($client)->not->toBeNull()
        ->and($client->name)->toBe('Test Auth Code Client');
});

test('creates authorization code pkce client', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Test PKCE Client',
        grantProfile: ClientResource::GrantAuthorizationCodePkce,
        redirectUris: ['https://example.com/callback'],
        provider: null,
        confidential: false,
        enableDeviceFlow: false,
    );

    expect($result->grantProfile)->toBe(ClientResource::GrantAuthorizationCodePkce)
        ->and($result->isConfidential)->toBeFalse()
        ->and($result->clientSecret)->toBeNull();
});

test('creates client credentials client', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Test CC Client',
        grantProfile: ClientResource::GrantClientCredentials,
        redirectUris: [],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );

    expect($result->grantProfile)->toBe(ClientResource::GrantClientCredentials)
        ->and($result->isConfidential)->toBeTrue()
        ->and($result->clientSecret)->not->toBeNull();
});

test('creates password grant client', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Test Password Client',
        grantProfile: ClientResource::GrantPassword,
        redirectUris: [],
        provider: 'users',
        confidential: true,
        enableDeviceFlow: false,
    );

    expect($result->grantProfile)->toBe(ClientResource::GrantPassword)
        ->and($result->isConfidential)->toBeTrue();
});

test('creates implicit grant client', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Test Implicit Client',
        grantProfile: ClientResource::GrantImplicit,
        redirectUris: ['https://example.com/callback'],
        provider: null,
        confidential: false,
        enableDeviceFlow: false,
    );

    expect($result->grantProfile)->toBe(ClientResource::GrantImplicit)
        ->and($result->isConfidential)->toBeFalse();
});

test('creates device authorization client', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Test Device Client',
        grantProfile: ClientResource::GrantDeviceAuthorization,
        redirectUris: [],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );

    expect($result->grantProfile)->toBe(ClientResource::GrantDeviceAuthorization)
        ->and($result->isConfidential)->toBeTrue();
});

test('creates authorization code client with device flow', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Test Auth + Device Client',
        grantProfile: ClientResource::GrantAuthorizationCode,
        redirectUris: ['https://example.com/callback'],
        provider: null,
        confidential: true,
        enableDeviceFlow: true,
    );

    $client = Passport::client()->find($result->clientId);
    $grantTypes = $client->grant_types ?? [];

    expect($grantTypes)->toContain('urn:ietf:params:oauth:grant-type:device_code');
});

test('throws on unsupported grant profile', function () {
    $manager = app(PassportClientManager::class);

    $manager->createClient(
        name: 'Bad Client',
        grantProfile: 'nonexistent_profile',
        redirectUris: [],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );
})->throws(RuntimeException::class, 'Unsupported Passport grant profile');

test('updates client name', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Original Name',
        grantProfile: ClientResource::GrantClientCredentials,
        redirectUris: [],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );

    $updated = $manager->updateClient($result->client, [
        'name' => 'Updated Name',
    ]);

    expect($updated->name)->toBe('Updated Name');
});

test('revokes and restores client', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Revocable Client',
        grantProfile: ClientResource::GrantClientCredentials,
        redirectUris: [],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );

    $manager->revokeClient($result->client);
    expect((bool) $result->client->revoked)->toBeTrue();

    $manager->restoreClient($result->client);
    expect((bool) $result->client->revoked)->toBeFalse();
});

test('regenerates client secret', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Secret Client',
        grantProfile: ClientResource::GrantClientCredentials,
        redirectUris: [],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );

    $newSecret = $manager->regenerateSecret($result->client);

    expect($newSecret)->not->toBeNull()->not->toBeEmpty();
});

test('revokes token and its refresh token', function () {
    $manager = app(PassportClientManager::class);

    $result = $manager->createClient(
        name: 'Token Client',
        grantProfile: ClientResource::GrantAuthorizationCode,
        redirectUris: ['https://example.com/callback'],
        provider: null,
        confidential: true,
        enableDeviceFlow: false,
    );

    $token = Passport::token()->forceCreate([
        'id' => \Illuminate\Support\Str::random(80),
        'user_id' => 1,
        'client_id' => $result->clientId,
        'scopes' => '[]',
        'revoked' => false,
        'expires_at' => now()->addDay(),
    ]);

    $refreshToken = \Laravel\Passport\RefreshToken::forceCreate([
        'id' => \Illuminate\Support\Str::random(80),
        'access_token_id' => $token->id,
        'revoked' => false,
        'expires_at' => now()->addDay(),
    ]);

    $manager->revokeToken($token);

    expect((bool) $token->fresh()->revoked)->toBeTrue()
        ->and((bool) $refreshToken->fresh()->revoked)->toBeTrue();
});
