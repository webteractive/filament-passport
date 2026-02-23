<?php

test('views config key defaults to enabled', function () {
    expect(config('filament-passport.views.enabled'))->toBeTrue();
});

test('views config key can be set to false', function () {
    config()->set('filament-passport.views.enabled', false);

    expect(config('filament-passport.views.enabled'))->toBeFalse();
});

test('individual view config keys have defaults', function () {
    expect(config('filament-passport.views.authorization'))->toBe('filament-passport::authorize');
    expect(config('filament-passport.views.device_authorization'))->toBe('filament-passport::device.authorize');
    expect(config('filament-passport.views.device_user_code'))->toBe('filament-passport::device.user-code');
});

test('individual view config keys can be set to null', function () {
    config()->set('filament-passport.views.authorization', null);
    config()->set('filament-passport.views.device_authorization', null);
    config()->set('filament-passport.views.device_user_code', null);

    expect(config('filament-passport.views.authorization'))->toBeNull();
    expect(config('filament-passport.views.device_authorization'))->toBeNull();
    expect(config('filament-passport.views.device_user_code'))->toBeNull();
});

test('individual view config keys can be set to custom view names', function () {
    config()->set('filament-passport.views.authorization', 'custom.authorize');
    config()->set('filament-passport.views.device_authorization', 'custom.device.authorize');
    config()->set('filament-passport.views.device_user_code', 'custom.device.user-code');

    expect(config('filament-passport.views.authorization'))->toBe('custom.authorize');
    expect(config('filament-passport.views.device_authorization'))->toBe('custom.device.authorize');
    expect(config('filament-passport.views.device_user_code'))->toBe('custom.device.user-code');
});
