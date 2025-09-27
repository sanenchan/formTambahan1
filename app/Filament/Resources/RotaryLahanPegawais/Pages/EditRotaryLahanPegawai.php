<?php

namespace App\Filament\Resources\RotaryLahanPegawais\Pages;

use App\Filament\Resources\RotaryLahanPegawais\RotaryLahanPegawaiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRotaryLahanPegawai extends EditRecord
{
    protected static string $resource = RotaryLahanPegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
