<?php

namespace App\Filament\Resources\ProduksiRotaries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProduksiRotaryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tanggal_produksi')
                    ->date(),
                TextEntry::make('id_target')
                    ->numeric(),
                TextEntry::make('jam_mulai_mesin')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('jam_selesai_mesin')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('status_produksi')
                    ->numeric(),
                TextEntry::make('kendala')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status_data')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
