<?php

namespace App\Filament\Resources\ProduksiRotaryLahans\Pages;

use App\Filament\Resources\ProduksiRotaryLahans\ProduksiRotaryLahanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProduksiRotaryLahans extends ListRecords
{
    protected static string $resource = ProduksiRotaryLahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
