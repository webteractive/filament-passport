<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Webteractive\FilamentPassport\Support\PkceFlow;
use Webteractive\FilamentPassport\Support\PkceCodePair;

// --- PkceCodePair ---

test('generates a code pair with 128-character verifier', function () {
    $pair = PkceCodePair::generate();

    expect($pair->verifier)->toHaveLength(128)
        ->and($pair->challenge)->not->toBeEmpty()
        ->and($pair->challenge)->not->toBe($pair->verifier);
});

test('generates a valid S256 challenge from verifier', function () {
    $pair = PkceCodePair::generate();

    $expected = rtrim(strtr(
        base64_encode(hash('sha256', $pair->verifier, true)),
        '+/', '-_',
    ), '=');

    expect($pair->challenge)->toBe($expected);
});

test('challenge contains only base64url characters', function () {
    $pair = PkceCodePair::generate();

    expect($pair->challenge)->toMatch('/^[A-Za-z0-9\-_]+$/');
});

test('each generated pair is unique', function () {
    $a = PkceCodePair::generate();
    $b = PkceCodePair::generate();

    expect($a->verifier)->not->toBe($b->verifier)
        ->and($a->challenge)->not->toBe($b->challenge);
});

// --- PkceFlow::redirect() ---

test('redirect stores state and code verifier in session', function () {
    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $flow->redirect();

    expect(session('pkce_state'))->not->toBeNull()->toHaveLength(40)
        ->and(session('pkce_code_verifier'))->not->toBeNull()->toHaveLength(128);
});

test('redirect returns redirect response to authorization url', function () {
    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $response = $flow->redirect();

    expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);

    $targetUrl = $response->getTargetUrl();

    expect($targetUrl)
        ->toStartWith('https://auth.example.com/oauth/authorize')
        ->toContain('client_id=test-client-id')
        ->toContain('response_type=code')
        ->toContain('code_challenge_method=S256')
        ->toContain('code_challenge=')
        ->toContain('state=');
});

test('redirect includes scopes in authorization url', function () {
    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    )->scopes(['read', 'write']);

    $response = $flow->redirect();
    $targetUrl = $response->getTargetUrl();

    expect($targetUrl)->toContain('scope=read+write');
});

test('redirect builds callback url from server url and callback path', function () {
    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $response = $flow->redirect();
    $targetUrl = $response->getTargetUrl();

    expect($targetUrl)->toContain(urlencode('https://auth.example.com/auth/callback'));
});

test('redirect uses full url when callback path is absolute', function () {
    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: 'https://myapp.example.com/auth/callback',
    );

    $response = $flow->redirect();
    $targetUrl = $response->getTargetUrl();

    expect($targetUrl)->toContain(urlencode('https://myapp.example.com/auth/callback'));
});

// --- PkceFlow::callback() ---

test('callback exchanges code for tokens', function () {
    Http::fake([
        'https://auth.example.com/oauth/token' => Http::response([
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
        ]),
    ]);

    session()->put('pkce_state', 'test-state-value');
    session()->put('pkce_code_verifier', 'test-code-verifier');

    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $request = Request::create('/auth/callback', 'GET', [
        'state' => 'test-state-value',
        'code' => 'test-auth-code',
    ]);

    $tokens = $flow->callback($request);

    expect($tokens)->toBeArray()
        ->and($tokens['access_token'])->toBe('test-access-token')
        ->and($tokens['refresh_token'])->toBe('test-refresh-token')
        ->and($tokens['token_type'])->toBe('Bearer')
        ->and($tokens['expires_in'])->toBe(3600);

    expect(session('pkce_state'))->toBeNull()
        ->and(session('pkce_code_verifier'))->toBeNull();
});

test('callback sends correct parameters to token endpoint', function () {
    Http::fake([
        'https://auth.example.com/oauth/token' => Http::response([
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
        ]),
    ]);

    session()->put('pkce_state', 'test-state');
    session()->put('pkce_code_verifier', 'the-code-verifier');

    $flow = PkceFlow::make(
        clientId: 'my-client',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $request = Request::create('/auth/callback', 'GET', [
        'state' => 'test-state',
        'code' => 'the-auth-code',
    ]);

    $flow->callback($request);

    Http::assertSent(function (\Illuminate\Http\Client\Request $httpRequest) {
        return $httpRequest->url() === 'https://auth.example.com/oauth/token'
            && $httpRequest['grant_type'] === 'authorization_code'
            && $httpRequest['client_id'] === 'my-client'
            && $httpRequest['code_verifier'] === 'the-code-verifier'
            && $httpRequest['code'] === 'the-auth-code'
            && $httpRequest['redirect_uri'] === 'https://auth.example.com/auth/callback';
    });
});

test('callback throws on state mismatch', function () {
    session()->put('pkce_state', 'correct-state');
    session()->put('pkce_code_verifier', 'verifier');

    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $request = Request::create('/auth/callback', 'GET', [
        'state' => 'wrong-state',
        'code' => 'test-auth-code',
    ]);

    $flow->callback($request);
})->throws(RuntimeException::class, 'Invalid OAuth state');

test('callback throws on missing session state', function () {
    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $request = Request::create('/auth/callback', 'GET', [
        'state' => 'some-state',
        'code' => 'test-auth-code',
    ]);

    $flow->callback($request);
})->throws(RuntimeException::class, 'Invalid OAuth state');

test('callback throws on failed token exchange', function () {
    Http::fake([
        'https://auth.example.com/oauth/token' => Http::response([
            'error' => 'invalid_grant',
            'error_description' => 'The authorization code has expired.',
        ], 400),
    ]);

    session()->put('pkce_state', 'test-state');
    session()->put('pkce_code_verifier', 'verifier');

    $flow = PkceFlow::make(
        clientId: 'test-client-id',
        serverUrl: 'https://auth.example.com',
        callbackPath: '/auth/callback',
    );

    $request = Request::create('/auth/callback', 'GET', [
        'state' => 'test-state',
        'code' => 'expired-code',
    ]);

    $flow->callback($request);
})->throws(RuntimeException::class, 'The authorization code has expired.');
