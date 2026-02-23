<?php

use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Contracts\DeviceUserCodeViewResponse;
use Laravel\Passport\Contracts\DeviceAuthorizationViewResponse;

test('authorization view contract is bound by default', function () {
    expect(app()->bound(AuthorizationViewResponse::class))->toBeTrue();
});

test('device authorization view contract is bound by default', function () {
    expect(app()->bound(DeviceAuthorizationViewResponse::class))->toBeTrue();
});

test('device user code view contract is bound by default', function () {
    expect(app()->bound(DeviceUserCodeViewResponse::class))->toBeTrue();
});

test('authorize view exists', function () {
    expect(view()->exists('filament-passport::authorize'))->toBeTrue();
});

test('device authorize view exists', function () {
    expect(view()->exists('filament-passport::device.authorize'))->toBeTrue();
});

test('device user-code view exists', function () {
    expect(view()->exists('filament-passport::device.user-code'))->toBeTrue();
});
