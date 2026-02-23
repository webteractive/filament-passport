<?php

namespace Webteractive\FilamentPassport\Resources\AuthCodes\Pages;

use Filament\Resources\Pages\ListRecords;
use Webteractive\FilamentPassport\Resources\AuthCodes\AuthCodeResource;

class ListAuthCodes extends ListRecords
{
    protected static string $resource = AuthCodeResource::class;
}
