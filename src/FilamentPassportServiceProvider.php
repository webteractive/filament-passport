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
        $view = config('filament-passport.views.authorization', 'filament-passport::authorize');

        if ($view) {
            Passport::authorizationView($view);
        }

        if (method_exists(Passport::class, 'deviceAuthorizationView')) {
            $deviceView = config('filament-passport.views.device_authorization', 'filament-passport::device.authorize');

            if ($deviceView) {
                Passport::deviceAuthorizationView($deviceView);
            }

            $userCodeView = config('filament-passport.views.device_user_code', 'filament-passport::device.user-code');

            if ($userCodeView) {
                Passport::deviceUserCodeView($userCodeView);
            }
        }
    }
}
