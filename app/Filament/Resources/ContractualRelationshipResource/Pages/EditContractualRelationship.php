<?php

namespace App\Filament\Resources\ContractualRelationshipResource\Pages;

use App\Filament\Resources\ContractualRelationshipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractualRelationship extends EditRecord
{
    protected static string $resource = ContractualRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
