<?php

use Webteractive\FilamentPassport\Resources\AccessTokens\AccessTokenResource;

test('cannot create access tokens', function () {
    expect(AccessTokenResource::canCreate())->toBeFalse();
});

test('model label comes from config', function () {
    expect(AccessTokenResource::getModelLabel())->toBe('Access Token');
    expect(AccessTokenResource::getPluralModelLabel())->toBe('Access Tokens');
});

test('navigation icon is set', function () {
    expect(AccessTokenResource::getNavigationIcon())->toBe('heroicon-o-ticket');
});

test('navigation group comes from config', function () {
    expect(AccessTokenResource::getNavigationGroup())->toBe('OAuth');
});

test('navigation sort is offset from base sort', function () {
    config()->set('filament-passport.navigation.sort', 10);

    expect(AccessTokenResource::getNavigationSort())->toBe(11);
});

test('navigation sort is null when base sort is null', function () {
    config()->set('filament-passport.navigation.sort', null);

    expect(AccessTokenResource::getNavigationSort())->toBeNull();
});
