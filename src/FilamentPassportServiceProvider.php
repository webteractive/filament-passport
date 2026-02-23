<?php

namespace Webteractive\FilamentPassport;

use Laravel\Passport\Passport;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentPassportServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-passport';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        if (config('filament-passport.views.enabled', true)) {
            $this->registerPassportViews();
        }
    }

    protected function registerPassportViews(): void
    {
        Passport::authorizationView('filament-passport::authorize');

        if (method_exists(Passport::class, 'deviceAuthorizationView')) {
            Passport::deviceAuthorizationView('filament-passport::device.authorize');
            Passport::deviceUserCodeView('filament-passport::device.user-code');
        }
    }
}
