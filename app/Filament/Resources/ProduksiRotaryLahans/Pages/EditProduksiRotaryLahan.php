<?php

namespace App\Filament\Resources\ProduksiRotaryLahans\Pages;

use App\Filament\Resources\ProduksiRotaryLahans\ProduksiRotaryLahanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduksiRotaryLahan extends EditRecord
{
    protected static string $resource = ProduksiRotaryLahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
