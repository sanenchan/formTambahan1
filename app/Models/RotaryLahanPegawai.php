<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RotaryLahanPegawai extends Model
{
    use HasFactory;

    protected $table = 'produksi_rotary_pegawais';
    protected $primaryKey = 'id_produksi_rotary_pegawai';

    protected $fillable = [
        'id_produksi_rotary',
        'id_pegawai',
        'jam_mulai',
        'jam_selesai',
        'potongan_target',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    public function produksiRotary()
    {
        return $this->belongsTo(ProduksiRotary::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }


}
