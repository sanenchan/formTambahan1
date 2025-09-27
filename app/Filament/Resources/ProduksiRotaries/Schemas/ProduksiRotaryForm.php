<?php

namespace App\Filament\Resources\ProduksiRotaries\Schemas;

use App\Models\JenisKayu;
use App\Models\Lahan;
use App\Models\Mesin;
use App\Models\Pegawai;
use App\Models\Target;
use App\Models\Ukuran;
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
        $allData = Cache::remember('form_data_all', now()->addHour(), function () {
            return [
                'mesin' => Mesin::where('id_kategori_mesin', 1)
                    ->pluck('nama_mesin', 'id_mesin')
                    ->toArray(),

                'ukuran' => Target::with('ukuranModel')
                    ->whereHas('mesin', fn($q) => $q->where('id_kategori_mesin', 1))
                    ->get()
                    ->groupBy('id_mesin')
                    ->map(fn($targets) => $targets->mapWithKeys(fn($target) => [
                        $target->id_ukuran => $target->ukuranModel->panjang
                            . ' x ' . $target->ukuranModel->lebar
                            . ' x ' . $target->ukuranModel->tebal,
                    ]))
                    ->toArray(),

                'jenis_kayu' => Target::with(['ukuranModel', 'mesin', 'jenisKayu'])
                    ->whereHas('mesin', fn($q) => $q->where('id_kategori_mesin', 1))
                    ->select('id_mesin', 'id_ukuran', 'id_jenis_kayu')
                    ->groupBy('id_mesin', 'id_ukuran', 'id_jenis_kayu')
                    ->get()
                    ->groupBy('id_mesin')
                    ->map(fn($targets) => $targets->groupBy('id_ukuran')->map(
                        fn($group) =>
                        $group->mapWithKeys(fn($target) => [
                            $target->id_jenis_kayu => (
                                $target->jenisKayu
                                ? $target->jenisKayu->kode_kayu . ' - ' . $target->jenisKayu->nama_kayu
                                : 'Tidak ada'
                            )
                        ])
                    ))
                    ->toArray(),
            ];
        });
        //Cache::forget('form_data_all'),
        //dd(Cache::get('form_data_all')),
        return $schema->components([


            // === DATA PRODUKSI ROTARY (induk) ===
            Section::make('Data Produksi Rotary')
                ->schema([
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
                        ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null),

                    // Jam selesai mesin
                    Select::make('jam_selesai_mesin')
                        ->label('Jam Selesai Mesin')
                        ->options(self::timeOptions())
                        ->default('17:00') // Default: 06:00 (pagi)
                        ->required()
                        ->searchable()
                        ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null),

                    Textarea::make('kendala')
                        ->columnSpanFull(),


                ])
                ->columns(3)
                ->columnSpanFull(),



            Section::make('Detail Produksi per Lahan')
                ->description('Input data per lahan yang diproduksi')
                ->schema([
                    Select::make('id_mesin')
                        ->label('Mesin')
                        ->options($allData['mesin'])
                        ->reactive()
                        ->required(),

                    Repeater::make('lahans')
                        ->relationship('lahans') // ✅ ini hasMany di ProduksiRotary
                        ->schema([
                            //menentukan Target
                            Select::make('id_ukuran')
                                ->label('Ukuran')
                                ->options(function (callable $get) use ($allData) {
                                    $idMesin = $get('../../id_mesin'); // ambil mesin dari parent
                                    return $idMesin
                                        ? ($allData['ukuran'][$idMesin] ?? [])
                                        : [];
                                })
                                ->reactive()
                                ->required(),

                            Select::make('id_jenis_kayu')
                                ->label('Jenis Kayu')
                                ->options(function (callable $get) use ($allData) {
                                    $idMesin = $get('../../id_mesin'); // ambil mesin dari parent
                                    $idUkuran = $get('id_ukuran');    // ambil ukuran dari item repeater
                                    return ($idMesin && $idUkuran)
                                        ? ($allData['jenis_kayu'][$idMesin][$idUkuran] ?? [])
                                        : [];
                                })
                                ->reactive()
                                ->required(),


                            Select::make('id_lahan')
                                ->label('Lahan')
                                ->searchable()
                                ->options(function ($search) {
                                    return Lahan::query()
                                        ->when($search, function ($query, $search) {
                                            $query->where('kode_lahan', 'like', "%{$search}%")
                                                ->orWhere('nama_lahan', 'like', "%{$search}%");
                                        })
                                        ->selectRaw('id_lahan, CONCAT(kode_lahan, " - ", nama_lahan) AS label')
                                        ->get()
                                        ->pluck('label', 'id_lahan'); // key = id_lahan, value = gabungan kode + nama
                                })
                                ->required(),
                            //List Lahan
                            TextInput::make('jumlah_batang')->numeric(),
                            TextInput::make('hasilkw1')->numeric(),
                            TextInput::make('hasilkw2')->numeric(),
                            TextInput::make('hasilkw3')->numeric(),
                            TextInput::make('hasilkw4')->numeric(),

                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Lahan'),
                ])
                ->collapsible()
                ->columnSpanFull(),

            // //===============================debug====================
            // Section::make('Debug Data Cache')
            //     ->schema([
            //         //== untuk load data

            //         //selesai Load data
            //         Html::make('debug')
            //             ->content(fn() => '<pre>' . json_encode(Cache::get('form_data_all', []), JSON_PRETTY_PRINT) . '</pre>')
            //     ])
            //     ->collapsible()
            //     ->collapsed() // biar default tertutup
            //     ->columnSpanFull(),

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
                                ->options(self::timeOptions())
                                ->default('06:00') // Default: 06:00 (pagi)
                                ->required()
                                ->searchable()
                                ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null),
                            Select::make('jam_selesai')
                                ->label('Jam Selesai')
                                ->options(self::timeOptions())
                                ->default('17:00') // Default: 17:00 (sore)
                                ->required()
                                ->searchable()
                                ->dehydrateStateUsing(fn($state) => $state ? $state . ':00' : null),


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



// Select::make('id_mesin')
//     ->label('Mesin')
//     ->options($allData['mesin'])
//     ->reactive()
//     ->afterStateUpdated(function (callable $set, $state) use ($allData) {
//         // reset dropdown turunan
//         $set('id_ukuran', null);
//         $set('id_jenis_kayu', null);
//     }),

// Select::make('id_ukuran')
//     ->label('Ukuran')
//     ->options(fn(callable $get) => $allData['ukuran'][$get('id_mesin')] ?? [])
//     ->reactive()
//     ->required(),

// Select::make('id_jenis_kayu')
//     ->label('Jenis Kayu')
//     ->options(fn(callable $get) => $allData['jenis_kayu'][$get('id_mesin')] ?? [])
//     ->required(),

////===============================debug====================
// Section::make('Debug Data Cache')
//     ->schema([
//         //== untuk load data

//         //selesai Load data
//         Html::make('debug')
//             ->content(fn() => '<pre>' . json_encode(Cache::get('form_data_all', []), JSON_PRETTY_PRINT) . '</pre>')
//     ])
//     ->collapsible()
//     ->collapsed() // biar default tertutup
//     ->columnSpanFull(),
//// === REPEATER UNTUK LAHAN ===