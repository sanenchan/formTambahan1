<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiRotary extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {

            // // 1️⃣ Hitung capaian produksi dari hasilkw1-4
            // $model->capaian_produksi = ($model->hasilkw1 ?? 0)
            //     + ($model->hasilkw2 ?? 0)
            //     + ($model->hasilkw3 ?? 0)
            //     + ($model->hasilkw4 ?? 0);

            // 2️⃣ Debug data dari model
            dd([
                'form_input' => request()->all(),       // input user
                'model_data' => $model->toArray(),      // data model, termasuk capaian_produksi
                'capaian_produksi' => $model->capaian_produksi, // bisa juga tampil sendiri
            ]);
        });
    }

    //
    use HasFactory;

    protected $primaryKey = 'id_produksi_rotary';

    protected $fillable = [
        'tanggal_produksi',
        'jam_mulai_mesin',
        'jam_selesai_mesin',
        'kendala',
        'status_data',
    ];

    public function lahans()
    {
        return $this->hasMany(ProduksiRotaryLahan::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }
    // sesuaikan Target model / kolom display

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }
    public function pegawaiProduksi()
    {
        return $this->hasMany(RotaryLahanPegawai::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }





}
