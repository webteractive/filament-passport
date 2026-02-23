<?php

namespace Webteractive\FilamentPassport\Resources\AccessTokens\Pages;

use Filament\Resources\Pages\ListRecords;
use Webteractive\FilamentPassport\Resources\AccessTokens\AccessTokenResource;

class ListAccessTokens extends ListRecords
{
    protected static string $resource = AccessTokenResource::class;
}
