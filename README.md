# Filament Passport

A [Filament](https://filamentphp.com) plugin to manage [Laravel Passport](https://laravel.com/docs/passport) OAuth clients, access tokens, and authorization codes. Ships with default OAuth consent views so Passport works out of the box.

> **Note:** Device flow (Device Authorization grant) requires **Laravel Passport v13**. All other features work with Passport v12 and v13.

## Requirements

- PHP 8.2+
- Filament 4.x or 5.x
- Laravel Passport 12.x or 13.x

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

This package provides default OAuth consent views so you don't have to build them yourself:

- **Authorization** — consent screen for OAuth authorization requests (v12 & v13)
- **Device Authorization** — consent screen for device flow (v13 only)
- **Device User Code** — form for entering a device code (v13 only)

### Customizing Views

Publish the views to your app and edit them:

```bash
php artisan vendor:publish --tag=filament-passport-views
```

This copies the views to `resources/views/vendor/filament-passport/` where they take precedence.

### Using Your Own Views

If you prefer to register views yourself via Passport's API, call `Passport::authorizationView()` or `Passport::viewPrefix()` in your `AppServiceProvider::boot()` — your bindings will overwrite the defaults.

```php
use Laravel\Passport\Passport;

public function boot(): void
{
    // Option 1: Set a view prefix — Passport will look for views
    // under resources/views/my-custom-views/ (e.g. my-custom-views.authorize)
    Passport::viewPrefix('my-custom-views.');

    // Option 2: Set a specific authorization view
    Passport::authorizationView('my-custom-views.authorize');
}
```

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
| Device Authorization *(v13 only)* | configurable | no | yes |
| Password | configurable | no | no |
| Implicit | no | required | no |
| Client Credentials | yes | no | no |

## PKCE Flow Helper

The plugin includes a ready-made helper for consuming apps that need to authenticate against a Passport server using the Authorization Code + PKCE flow. No client secret required — ideal for SPAs and mobile apps.

```php
use Illuminate\Http\Request;
use Webteractive\FilamentPassport\Support\PkceFlow;

$pkce = PkceFlow::make(
    clientId: config('services.passport.client_id'),
    serverUrl: config('services.passport.url'),
    callbackPath: '/auth/callback',
)->scopes(['read-posts', 'create-posts']);

// Redirect to the Passport authorization screen
Route::get('/auth/redirect', fn () => $pkce->redirect());

// Exchange the authorization code for tokens
Route::get('/auth/callback', function (Request $request) use ($pkce) {
    $tokens = $pkce->callback($request);

    // $tokens contains access_token, refresh_token, token_type, expires_in
    session(['api_token' => $tokens['access_token']]);

    return redirect('/dashboard');
});
```

The `callbackPath` can be a relative path (appended to `serverUrl`) or a full URL for cross-domain callbacks:

```php
// Same-domain callback
PkceFlow::make('client-id', 'https://auth.example.com', '/auth/callback');

// Cross-domain callback
PkceFlow::make('client-id', 'https://auth.example.com', 'https://myapp.com/auth/callback');
```

Under the hood, `redirect()` generates a cryptographic code verifier and challenge (S256), stores them in the session, and redirects to `/oauth/authorize`. The `callback()` method validates the state parameter, exchanges the authorization code at `/oauth/token`, and returns the token response.

You can also use `PkceCodePair` directly if you need just the verifier and challenge:

```php
use Webteractive\FilamentPassport\Support\PkceCodePair;

$pair = PkceCodePair::generate();
$pair->verifier;  // 128-character random string
$pair->challenge; // base64url-encoded SHA-256 hash
```

## Testing

```bash
composer test
```

## License

MIT. See [LICENSE](LICENSE) for details.
