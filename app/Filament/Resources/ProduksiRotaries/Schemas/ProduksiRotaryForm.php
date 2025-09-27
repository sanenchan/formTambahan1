<?php

namespace App\Filament\Resources\ProduksiRotaries\Schemas;

use App\Models\Lahan;
use App\Models\Pegawai;
use App\Models\Target;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Cache;
class ProduksiRotaryForm
{

    public static function configure(Schema $schema): Schema
    {
        //Cache::forget('form_data_all'),
        //dd(Cache::get('form_data_all')),
        return $schema->components([


            // === DATA PRODUKSI ROTARY (induk) ===
            Section::make('Data Produksi Rotary')
                ->schema([
                    Hidden::make('status_data')
                        ->default(1),
                    DatePicker::make('tanggal_produksi')
                        ->label('Tanggal Produksi')
                        ->required()
                        ->default(fn() => Carbon::yesterday())
                        ->displayFormat('j F Y') // untuk tampilan, misal 1 Januari 2025
                        ->native(false),         // penting, supaya pakai flatpickr (bukan native browser)
                    // Jam mulai mesin
                    Select::make('jam_mulai_mesin')
                        ->label('Jam Mulai Mesin')
                        ->options(self::timeOptions())
                        ->default('06:00') // Default: 06:00 (pagi)
                        ->required()
                        ->searchable()
                        ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null)
                        ->formatStateUsing(fn($state) => $state ? substr($state, 0, 5) : null), // Tampilkan hanya HH:MM,,

                    // Jam selesai mesin
                    Select::make('jam_selesai_mesin')
                        ->label('Jam Selesai Mesin')
                        ->options(self::timeOptions())
                        ->default('17:00') // Default: 06:00 (pagi)
                        ->required()
                        ->searchable()
                        ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null)
                        ->formatStateUsing(fn($state) => $state ? substr($state, 0, 5) : null), // Tampilkan hanya HH:MM,,

                    Textarea::make('kendala')
                        ->columnSpanFull(),


                ])
                ->columns(3)
                ->columnSpanFull(),


            //==== detail produksi per Lahan
            Section::make('Detail Produksi per Lahan')
                ->description('Input data per lahan yang diproduksi')
                ->schema([

                    Repeater::make('lahanProduksi')
                        ->relationship('lahanProduksi') // ✅ ini hasMany di ProduksiRotary
                        ->schema([
                            //menentukan Target
// Pilih target
                            Select::make('id_target')
                                ->label('Target')
                                ->options(Target::pluck('ukuran', 'id_target'))
                                ->searchable()
                                ->required(),

                            // Pilih lahan
                            Select::make('id_lahan')
                                ->label('Lahan')
                                ->options(Lahan::pluck('nama_lahan', 'id_lahan'))
                                ->searchable()
                                ->required(),

                            TextInput::make('jumlah_batang')
                                ->numeric()
                                ->required(),

                            TextInput::make('hasilkw1')
                                ->numeric(),

                            TextInput::make('hasilkw2')
                                ->numeric(),

                            TextInput::make('hasilkw3')
                                ->numeric(),

                            TextInput::make('hasilkw4')
                                ->numeric(),
                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Lahan'),
                ])
                ->collapsible()
                ->columnSpanFull(),


            // // === REPEATER UNTUK PEGAWAI PRODUKSI ===
            Section::make('Tenaga Kerja Produksi')
                ->schema([
                    Repeater::make('pegawaiProduksi')
                        ->relationship('pegawaiProduksi') // ✅ hasMany ke RotaryLahanPegawai
                        ->schema([
                            //cari pegawai
                            Select::make('id_pegawai')
                                ->label('Pegawai')
                                ->searchable()
                                ->options(function ($search) {
                                    return Pegawai::query()
                                        ->when($search, function ($query, $search) {
                                            $query->where('kode_pegawai', 'like', "%{$search}%")
                                                ->orWhere('nama_pegawai', 'like', "%{$search}%");
                                        })
                                        ->selectRaw('id_pegawai, CONCAT(kode_pegawai, " - ", nama_pegawai) AS label')
                                        ->get()
                                        ->pluck('label', 'id_pegawai'); // key = id, value = gabungan
                                })
                                ->required(),
                            //==end cari pegawai
                            Select::make('jam_mulai')
                                ->label('Jam Mulai')
                                ->options(self::timeOptions()) // misalnya ['06:00' => '06:00', dst.]
                                ->default('06:00')
                                ->required()
                                ->searchable()
                                ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null) // Simpan ke DB dengan detik
                                ->formatStateUsing(fn($state) => $state ? substr($state, 0, 5) : null), // Tampilkan hanya HH:MM,
                            Select::make('jam_selesai')
                                ->label('Jam Selesai')
                                ->options(self::timeOptions())
                                ->default('17:00') // Default: 17:00 (sore)
                                ->required()
                                ->searchable()
                                ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null)
                                ->formatStateUsing(fn($state) => $state ? substr($state, 0, 5) : null), // Tampilkan hanya HH:MM,


                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Pegawai'),
                ])
                ->collapsible()

                ->columnSpanFull(),

        ]);
    }

    /**
     * Generate time options every 30 minutes from 00:00 to 23:30.
     */
    /**
     * Generate time options every hour from 00:00 to 23:00.
     */
    public static function timeOptions(): array
    {
        return collect(CarbonPeriod::create('00:00', '1 hour', '23:00')->toArray())
            ->mapWithKeys(fn($time) => [
                $time->format('H:i') => $time->format('H.i'), // Key: H:i (e.g., '00:00'), Value: H.i (e.g., '0.0') for display
            ])
            ->toArray();
    }

}

// Select::make('id_target')
//                                 ->label('Target')
//                                 ->options(
//                                     Target::all()->pluck('ukuran', 'id_target')
//                                 )
//                                 ->searchable()
//                                 ->required(),

//                             Select::make('id_lahan')
//                                 ->label('Lahan')
//                                 ->searchable()
//                                 ->options(function ($search) {
//                                     return Lahan::query()
//                                         ->when($search, function ($query, $search) {
//                                             $query->where('kode_lahan', 'like', "%{$search}%")
//                                                 ->orWhere('nama_lahan', 'like', "%{$search}%");
//                                         })
//                                         ->selectRaw('id_lahan, CONCAT(kode_lahan, " - ", nama_lahan) AS label')
//                                         ->get()
//                                         ->pluck('label', 'id_lahan'); // key = id_lahan, value = gabungan kode + nama
//                                 })
//                                 ->required(),
//                             //List Lahan
//                             TextInput::make('jumlah_batang')->numeric(),
//                             TextInput::make('hasilkw1')->numeric(),
//                             TextInput::make('hasilkw2')->numeric(),
//                             TextInput::make('hasilkw3')->numeric(),
//                             TextInput::make('hasilkw4')->numeric(),
