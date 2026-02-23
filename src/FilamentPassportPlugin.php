<?php

namespace Webteractive\FilamentPassport;

use Closure;
use Filament\Panel;
use Filament\Contracts\Plugin;
use Illuminate\Support\Facades\Gate;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;
use Webteractive\FilamentPassport\Resources\AuthCodes\AuthCodeResource;
use Webteractive\FilamentPassport\Resources\AccessTokens\AccessTokenResource;

class FilamentPassportPlugin implements Plugin
{
    protected bool $hasClientResource = true;

    protected bool $hasAccessTokenResource = true;

    protected bool $hasAuthCodeResource = true;

    protected ?Closure $canAccessUsing = null;

    public function getId(): string
    {
        return 'filament-passport';
    }

    public function register(Panel $panel): void
    {
        $resources = [];

        if ($this->hasClientResource && config('filament-passport.resources.client.enabled', true)) {
            $resources[] = ClientResource::class;
        }

        if ($this->hasAccessTokenResource && config('filament-passport.resources.access_token.enabled', true)) {
            $resources[] = AccessTokenResource::class;
        }

        if ($this->hasAuthCodeResource && config('filament-passport.resources.auth_code.enabled', true)) {
            $resources[] = AuthCodeResource::class;
        }

        $panel->resources($resources);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return new static;
    }

    public function clients(bool $condition = true): static
    {
        $this->hasClientResource = $condition;

        return $this;
    }

    public function accessTokens(bool $condition = true): static
    {
        $this->hasAccessTokenResource = $condition;

        return $this;
    }

    public function authCodes(bool $condition = true): static
    {
        $this->hasAuthCodeResource = $condition;

        return $this;
    }

    public function canAccess(Closure $callback): static
    {
        $this->canAccessUsing = $callback;

        return $this;
    }

    public function authorized(): bool
    {
        if (Gate::has('viewFilamentPassport')) {
            return Gate::check('viewFilamentPassport');
        }

        if ($this->canAccessUsing) {
            return (bool) app()->call($this->canAccessUsing);
        }

        return true;
    }
}
