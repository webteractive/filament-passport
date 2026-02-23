<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('filament-passport::filament-passport.oauth.device_authorization_request') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { background: #fff; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; max-width: 26rem; width: 100%; }
        .title { font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem; }
        .description { color: #6b7280; font-size: 0.938rem; line-height: 1.5; margin-bottom: 1.5rem; }
        .description strong { color: #111827; }
        .scopes-label { font-weight: 500; font-size: 0.875rem; color: #374151; margin-bottom: 0.5rem; }
        .scopes-list { list-style: disc; padding-left: 1.25rem; margin-bottom: 1.5rem; }
        .scopes-list li { color: #6b7280; font-size: 0.875rem; padding: 0.15rem 0; }
        .actions { display: flex; gap: 0.75rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; border: none; transition: background 0.15s; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="title">{{ __('filament-passport::filament-passport.oauth.device_authorization_request') }}</h2>
        <p class="description">
            {!! __('filament-passport::filament-passport.oauth.requesting_permission', ['client' => '<strong>' . e($client->name) . '</strong>']) !!}
        </p>

        @if (count($scopes) > 0)
            <p class="scopes-label">{{ __('filament-passport::filament-passport.oauth.this_app_will_be_able_to') }}</p>
            <ul class="scopes-list">
                @foreach ($scopes as $scope)
                    <li>{{ $scope->description }}</li>
                @endforeach
            </ul>
        @endif

        <div class="actions">
            <form method="POST" action="{{ route('passport.device.authorizations.approve') }}">
                @csrf
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn btn-primary">
                    {{ __('filament-passport::filament-passport.oauth.authorize') }}
                </button>
            </form>

            <form method="POST" action="{{ route('passport.device.authorizations.deny') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn btn-secondary">
                    {{ __('filament-passport::filament-passport.oauth.deny') }}
                </button>
            </form>
        </div>
    </div>
</body>
</html>
