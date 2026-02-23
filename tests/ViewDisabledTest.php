<?php

test('views config key defaults to enabled', function () {
    expect(config('filament-passport.views.enabled'))->toBeTrue();
});

test('views config key can be set to false', function () {
    config()->set('filament-passport.views.enabled', false);

    expect(config('filament-passport.views.enabled'))->toBeFalse();
});
