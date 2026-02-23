<?php

use Webteractive\FilamentPassport\FilamentPassportServiceProvider;

test('service provider is registered', function () {
    expect(app()->getProviders(FilamentPassportServiceProvider::class))
        ->toHaveCount(1);
});

test('config file is published', function () {
    expect(config('filament-passport'))
        ->toBeArray()
        ->toHaveKeys(['navigation', 'resources']);
});

test('translations are loaded', function () {
    expect(__('filament-passport::filament-passport.client.sections.details'))
        ->toBe('Client Details');
});
