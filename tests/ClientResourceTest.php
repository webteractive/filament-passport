<?php

use Webteractive\FilamentPassport\Resources\Clients\ClientResource;

test('grant profile options returns all profiles', function () {
    $options = ClientResource::getGrantProfileOptions();

    expect($options)
        ->toBeArray()
        ->toHaveKeys([
            ClientResource::GrantAuthorizationCode,
            ClientResource::GrantAuthorizationCodePkce,
            ClientResource::GrantDeviceAuthorization,
            ClientResource::GrantPassword,
            ClientResource::GrantImplicit,
            ClientResource::GrantClientCredentials,
        ]);
});

test('grant profile requires redirect uris for correct profiles', function () {
    expect(ClientResource::grantProfileRequiresRedirectUris(ClientResource::GrantAuthorizationCode))->toBeTrue();
    expect(ClientResource::grantProfileRequiresRedirectUris(ClientResource::GrantAuthorizationCodePkce))->toBeTrue();
    expect(ClientResource::grantProfileRequiresRedirectUris(ClientResource::GrantImplicit))->toBeTrue();
    expect(ClientResource::grantProfileRequiresRedirectUris(ClientResource::GrantPassword))->toBeFalse();
    expect(ClientResource::grantProfileRequiresRedirectUris(ClientResource::GrantClientCredentials))->toBeFalse();
    expect(ClientResource::grantProfileRequiresRedirectUris(ClientResource::GrantDeviceAuthorization))->toBeFalse();
    expect(ClientResource::grantProfileRequiresRedirectUris(null))->toBeFalse();
});

test('parseRedirectUris handles array input', function () {
    $result = ClientResource::parseRedirectUris([
        'https://example.com/callback',
        'https://example.com/other',
        '',
        '  ',
    ]);

    expect($result)->toBe([
        'https://example.com/callback',
        'https://example.com/other',
    ]);
});

test('parseRedirectUris handles string input with commas', function () {
    $result = ClientResource::parseRedirectUris(
        'https://example.com/callback,https://example.com/other'
    );

    expect($result)->toBe([
        'https://example.com/callback',
        'https://example.com/other',
    ]);
});

test('parseRedirectUris handles string input with newlines', function () {
    $result = ClientResource::parseRedirectUris(
        "https://example.com/callback\nhttps://example.com/other"
    );

    expect($result)->toBe([
        'https://example.com/callback',
        'https://example.com/other',
    ]);
});

test('parseRedirectUris deduplicates entries', function () {
    $result = ClientResource::parseRedirectUris([
        'https://example.com/callback',
        'https://example.com/callback',
    ]);

    expect($result)->toBe(['https://example.com/callback']);
});

test('parseRedirectUris returns empty array for null', function () {
    expect(ClientResource::parseRedirectUris(null))->toBe([]);
});

test('parseRedirectUris returns empty array for empty string', function () {
    expect(ClientResource::parseRedirectUris(''))->toBe([]);
});

test('navigation group comes from config', function () {
    expect(ClientResource::getNavigationGroup())->toBe('OAuth');
});

test('model label comes from config', function () {
    expect(ClientResource::getModelLabel())->toBe('Client');
    expect(ClientResource::getPluralModelLabel())->toBe('Clients');
});

test('navigation icon is set', function () {
    expect(ClientResource::getNavigationIcon())->toBe('heroicon-o-key');
});
