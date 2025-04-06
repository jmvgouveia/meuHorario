<?php

namespace App\Filament\Resources\ContractualRelationshipResource\Pages;

use App\Filament\Resources\ContractualRelationshipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractualRelationships extends ListRecords
{
    protected static string $resource = ContractualRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
