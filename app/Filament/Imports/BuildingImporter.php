<?php

namespace App\Filament\Imports;

use App\Models\Building;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class BuildingImporter extends Importer
{
    protected static ?string $model = Building::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome')
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    'min:3',
                    // Esta é a forma correta de validar duplicados no Filament
                    Rule::unique(Building::class, 'name'),
                ])
                ->example('Edifício Central'),

            ImportColumn::make('address')
                ->label('Morada')
                ->rules([
                    'required',
                    'string',
                    'max:65535',
                ])
                ->example('Rua das Flores, 123, Lisboa'),
        ];
    }

    public function resolveRecord(): ?Building
    {
        // O Filament já faz as validações das rules automaticamente
        // Se chegou aqui, os dados são válidos
        return new Building();
    }

    // Método para processar os dados antes de salvar (opcional)
    protected function beforeFill(): void
    {
        // Limpa espaços em branco
        $this->data['name'] = trim($this->data['name'] ?? '');
        $this->data['address'] = trim($this->data['address'] ?? '');
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $successful = $import->successful_rows;
        $failed = $import->failed_rows;
        $total = $import->total_rows;

        if ($successful === 0) {
            return "Nenhum edifício foi importado. {$failed} registos falharam de {$total} processados.";
        }

        $message = "Importação concluída: {$successful} edifícios importados com sucesso";

        if ($failed > 0) {
            $message .= ", {$failed} falharam";
        }

        $message .= " de {$total} registos processados.";

        return $message;
    }
}
