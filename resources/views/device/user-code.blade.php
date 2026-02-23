<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('filament-passport::filament-passport.oauth.enter_code') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1rem; }
        .card { background: #fff; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem; max-width: 26rem; width: 100%; }
        .title { font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem; }
        .description { color: #6b7280; font-size: 0.938rem; line-height: 1.5; margin-bottom: 1.5rem; }
        label { display: block; font-weight: 500; font-size: 0.875rem; color: #374151; margin-bottom: 0.375rem; }
        input[type="text"] { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; letter-spacing: 0.05em; text-transform: uppercase; outline: none; transition: border-color 0.15s; }
        input[type="text"]:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.2); }
        input[type="text"].error { border-color: #ef4444; }
        .error-text { color: #ef4444; font-size: 0.813rem; margin-top: 0.375rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.5rem 1.25rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; border: none; background: #2563eb; color: #fff; margin-top: 1rem; transition: background 0.15s; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="title">{{ __('filament-passport::filament-passport.oauth.enter_code') }}</h2>
        <p class="description">{{ __('filament-passport::filament-passport.oauth.enter_code_description') }}</p>

        <form method="GET" action="{{ route('passport.device') }}">
            <label for="user_code">{{ __('filament-passport::filament-passport.oauth.user_code') }}</label>
            <input
                type="text"
                id="user_code"
                name="user_code"
                value="{{ old('user_code') }}"
                placeholder="{{ __('filament-passport::filament-passport.oauth.user_code_placeholder') }}"
                autofocus
                required
                @error('user_code') class="error" @enderror
            >
            @error('user_code')
                <p class="error-text">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn">
                {{ __('filament-passport::filament-passport.oauth.continue') }}
            </button>
        </form>
    </div>
</body>
</html>
