<?php

namespace Webteractive\FilamentPassport\Support;

use Illuminate\Database\Eloquent\Model;

class PassportClientCreationResult
{
    /**
     * @param  array<int, string>  $redirectUris
     */
    public function __construct(
        public readonly Model $client,
        public readonly string $grantProfile,
        public readonly string $clientId,
        public readonly ?string $clientSecret,
        public readonly bool $isConfidential,
        public readonly array $redirectUris,
    ) {}
}
