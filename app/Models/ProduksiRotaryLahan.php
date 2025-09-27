<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiRotaryLahan extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_rotary_lahan';

    protected $fillable = [
        'id_produksi_rotary',
        'id_lahan',
        'jumlah_batang',
        'kubikasi',
        'hasilkw1',
        'hasilkw2',
        'hasilkw3',
        'hasilkw4',
        'target_produksi',
        'status_produksi',
        'capaian_produksi',
        'potongan_target',
    ];

    public function produksi()
    {
        return $this->belongsTo(ProduksiRotary::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }
    public function target()
    {
        return $this->belongsTo(Target::class, 'id_target', 'id_target');
    }
    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan');
    }

    public function pegawai()
    {
        return $this->hasMany(RotaryLahanPegawai::class, 'id_rotary_lahan', 'id_rotary_lahan');
    }
}
