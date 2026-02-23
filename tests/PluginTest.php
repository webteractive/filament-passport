<?php

use Webteractive\FilamentPassport\FilamentPassportPlugin;

test('plugin can be instantiated', function () {
    $plugin = FilamentPassportPlugin::make();

    expect($plugin)
        ->toBeInstanceOf(FilamentPassportPlugin::class)
        ->and($plugin->getId())
        ->toBe('filament-passport');
});

test('plugin is authorized by default', function () {
    $plugin = FilamentPassportPlugin::make();

    expect($plugin->authorized())->toBeTrue();
});

test('plugin respects canAccess callback', function () {
    $plugin = FilamentPassportPlugin::make()
        ->canAccess(fn () => false);

    expect($plugin->authorized())->toBeFalse();
});

test('plugin respects gate when defined', function () {
    \Illuminate\Support\Facades\Gate::define('viewFilamentPassport', fn () => false);

    $plugin = FilamentPassportPlugin::make();

    expect($plugin->authorized())->toBeFalse();
});

test('plugin can toggle resource flags', function () {
    $plugin = FilamentPassportPlugin::make()
        ->clients(false)
        ->accessTokens(false)
        ->authCodes(false);

    expect($plugin)->toBeInstanceOf(FilamentPassportPlugin::class);
});
