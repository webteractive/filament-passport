<?php

namespace Webteractive\FilamentPassport\Support;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;

class PkceFlow
{
    protected string $clientId;

    protected string $serverUrl;

    protected string $callbackPath;

    /** @var array<int, string> */
    protected array $scopes = [];

    public static function make(
        string $clientId,
        string $serverUrl,
        string $callbackPath,
    ): static {
        $instance = new static;
        $instance->clientId = $clientId;
        $instance->serverUrl = rtrim($serverUrl, '/');
        $instance->callbackPath = $callbackPath;

        return $instance;
    }

    /**
     * @param  array<int, string>  $scopes
     */
    public function scopes(array $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * Generate PKCE pair, store state and verifier in session,
     * and return a redirect response to the authorization endpoint.
     */
    public function redirect(): RedirectResponse
    {
        $codePair = PkceCodePair::generate();
        $state = Str::random(40);

        session()->put('pkce_state', $state);
        session()->put('pkce_code_verifier', $codePair->verifier);

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->buildCallbackUrl(),
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes),
            'state' => $state,
            'code_challenge' => $codePair->challenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect("{$this->serverUrl}/oauth/authorize?{$query}");
    }

    /**
     * Validate state and exchange the authorization code for tokens.
     *
     * @return array{token_type: string, expires_in: int, access_token: string, refresh_token: string}
     *
     * @throws RuntimeException
     */
    public function callback(Request $request): array
    {
        $storedState = session()->pull('pkce_state');
        $codeVerifier = session()->pull('pkce_code_verifier');

        if (! $storedState || $request->input('state') !== $storedState) {
            throw new RuntimeException('Invalid OAuth state. Possible CSRF attack.');
        }

        $response = Http::asForm()->post("{$this->serverUrl}/oauth/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->buildCallbackUrl(),
            'code_verifier' => $codeVerifier,
            'code' => $request->input('code'),
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'PKCE token exchange failed: '.($response->json('error_description') ?? $response->body())
            );
        }

        return $response->json();
    }

    protected function buildCallbackUrl(): string
    {
        if (str_starts_with($this->callbackPath, 'http://') || str_starts_with($this->callbackPath, 'https://')) {
            return $this->callbackPath;
        }

        return $this->serverUrl.'/'.ltrim($this->callbackPath, '/');
    }
}
