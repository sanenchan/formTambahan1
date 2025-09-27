<?php

namespace App\Filament\Resources\RotaryLahanPegawais\Pages;

use App\Filament\Resources\RotaryLahanPegawais\RotaryLahanPegawaiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRotaryLahanPegawais extends ListRecords
{
    protected static string $resource = RotaryLahanPegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
