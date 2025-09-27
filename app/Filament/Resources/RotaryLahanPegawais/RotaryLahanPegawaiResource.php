<?php

namespace App\Filament\Resources\RotaryLahanPegawais;

use App\Filament\Resources\RotaryLahanPegawais\Pages\CreateRotaryLahanPegawai;
use App\Filament\Resources\RotaryLahanPegawais\Pages\EditRotaryLahanPegawai;
use App\Filament\Resources\RotaryLahanPegawais\Pages\ListRotaryLahanPegawais;
use App\Filament\Resources\RotaryLahanPegawais\Schemas\RotaryLahanPegawaiForm;
use App\Filament\Resources\RotaryLahanPegawais\Tables\RotaryLahanPegawaisTable;
use App\Models\RotaryLahanPegawai;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RotaryLahanPegawaiResource extends Resource
{
    protected static ?string $model = RotaryLahanPegawai::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'potongan_target';

    public static function form(Schema $schema): Schema
    {
        return RotaryLahanPegawaiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RotaryLahanPegawaisTable::configure($table);
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
            'index' => ListRotaryLahanPegawais::route('/'),
            'create' => CreateRotaryLahanPegawai::route('/create'),
            'edit' => EditRotaryLahanPegawai::route('/{record}/edit'),
        ];
    }
}
