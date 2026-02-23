<?php

namespace Webteractive\FilamentPassport\Support;

use Illuminate\Support\Str;

class PkceCodePair
{
    public function __construct(
        public readonly string $verifier,
        public readonly string $challenge,
    ) {}

    public static function generate(): static
    {
        $verifier = Str::random(128);

        $challenge = rtrim(strtr(
            base64_encode(hash('sha256', $verifier, true)),
            '+/', '-_',
        ), '=');

        return new static(
            verifier: $verifier,
            challenge: $challenge,
        );
    }
}
