<?php

namespace Webteractive\FilamentPassport\Resources\Clients\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Webteractive\FilamentPassport\Resources\Clients\ClientResource;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
