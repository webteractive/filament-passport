<?php

use Webteractive\FilamentPassport\Resources\AuthCodes\AuthCodeResource;

test('cannot create auth codes', function () {
    expect(AuthCodeResource::canCreate())->toBeFalse();
});

test('model label comes from config', function () {
    expect(AuthCodeResource::getModelLabel())->toBe('Auth Code');
    expect(AuthCodeResource::getPluralModelLabel())->toBe('Auth Codes');
});

test('navigation icon is set', function () {
    expect(AuthCodeResource::getNavigationIcon())->toBe('heroicon-o-document-check');
});

test('navigation sort is offset from base sort', function () {
    config()->set('filament-passport.navigation.sort', 10);

    expect(AuthCodeResource::getNavigationSort())->toBe(12);
});
