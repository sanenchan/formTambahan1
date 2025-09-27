<?php

namespace App\Filament\Resources\ProduksiRotaryLahans;

use App\Filament\Resources\ProduksiRotaryLahans\Pages\CreateProduksiRotaryLahan;
use App\Filament\Resources\ProduksiRotaryLahans\Pages\EditProduksiRotaryLahan;
use App\Filament\Resources\ProduksiRotaryLahans\Pages\ListProduksiRotaryLahans;
use App\Filament\Resources\ProduksiRotaryLahans\Schemas\ProduksiRotaryLahanForm;
use App\Filament\Resources\ProduksiRotaryLahans\Tables\ProduksiRotaryLahansTable;
use App\Models\ProduksiRotaryLahan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProduksiRotaryLahanResource extends Resource
{
    protected static ?string $model = ProduksiRotaryLahan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'jumlah_batang';

    public static function form(Schema $schema): Schema
    {
        return ProduksiRotaryLahanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProduksiRotaryLahansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProduksiRotaryLahans::route('/'),
            'create' => CreateProduksiRotaryLahan::route('/create'),
            'edit' => EditProduksiRotaryLahan::route('/{record}/edit'),
        ];
    }
}
