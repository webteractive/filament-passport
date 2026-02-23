# Filament Passport

A [Filament](https://filamentphp.com) plugin to manage [Laravel Passport](https://laravel.com/docs/passport) OAuth clients, access tokens, and authorization codes. Ships with default OAuth consent views so Passport works out of the box.

> **Note:** This package requires **Laravel Passport v13** (headless). Passport v12 and below are not supported.

## Requirements

- PHP 8.2+
- Filament 4.x or 5.x
- Laravel Passport 13.x

## Installation

Make sure [Laravel Passport](https://laravel.com/docs/passport#installation) is installed and migrated first:

```bash
php artisan install:api --passport
```

Then install the plugin:

```bash
composer require webteractive/filament-passport
```

Register the plugin in your panel provider:

```php
use Webteractive\FilamentPassport\FilamentPassportPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentPassportPlugin::make(),
        ]);
}
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=filament-passport-config
```

## Authorization Views

Passport v13 is headless and does not include any UI. This package provides default OAuth consent views so you don't have to build them yourself:

- **Authorization** — consent screen for OAuth authorization requests
- **Device Authorization** — consent screen for device flow
- **Device User Code** — form for entering a device code

### Customizing Views

Publish the views to your app and edit them:

```bash
php artisan vendor:publish --tag=filament-passport-views
```

This copies the views to `resources/views/vendor/filament-passport/` where they take precedence.

### Using Your Own Views

If you prefer to register views yourself via Passport's API, call `Passport::authorizationView()` or `Passport::viewPrefix()` in your `AppServiceProvider::boot()` — your bindings will overwrite the defaults.

To skip the default bindings entirely:

```php
// config/filament-passport.php
'views' => [
    'enabled' => false,
],
```

## Configuration

### Toggle Resources

Disable any resource via config or fluently on the plugin:

```php
// config/filament-passport.php
'resources' => [
    'client'       => ['enabled' => true],
    'access_token' => ['enabled' => true],
    'auth_code'    => ['enabled' => false],
],
```

```php
// Or in your panel provider
FilamentPassportPlugin::make()
    ->clients()
    ->accessTokens()
    ->authCodes(false),
```

### Navigation

```php
// config/filament-passport.php
'navigation' => [
    'group' => 'OAuth',
    'sort'  => null,
],
```

### Access Control

The plugin checks authorization in this order:

1. **Gate** — if a `viewFilamentPassport` gate is defined, it is used.
2. **Callback** — a closure passed via `canAccess()`.
3. **Default** — open access (returns `true`).

```php
// Gate
Gate::define('viewFilamentPassport', fn ($user) => $user->isAdmin());

// Or callback
FilamentPassportPlugin::make()
    ->canAccess(fn () => auth()->user()->isAdmin()),
```

## Supported Grant Profiles

| Profile | Confidential | Redirect URIs | Device Flow |
|---|---|---|---|
| Authorization Code | configurable | required | optional |
| Authorization Code (PKCE) | no | required | no |
| Device Authorization | configurable | no | yes |
| Password | configurable | no | no |
| Implicit | no | required | no |
| Client Credentials | yes | no | no |

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE](LICENSE) for details.
