<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokKayu extends Model
{
    use HasFactory;

    // Nama tabel (opsional, hanya jika berbeda dari plural bawaan Laravel)
    protected $table = 'stok_kayus';

    // Primary key
    protected $primaryKey = 'id_stok_kayu';

    // Kalau tidak pakai increment id standar
    public $incrementing = true;
    protected $keyType = 'int';

    // Kolom yang bisa diisi (fillable)
    protected $fillable = [
        'id_lahan',
        'id_jenis_kayu',
        'panjang',
        'jumlah_bata',
        'kubikasi',
        'harga',
        'tanggal_masuk',
        'detail_nomor_seri',
    ];

    // Relasi ke tabel Lahan
    public function lahan()
    {
        return $this->belongsTo(\App\Models\Lahan::class, 'id_lahan', 'id_lahan');
    }

    public function jenisKayu()
    {
        return $this->belongsTo(\App\Models\JenisKayu::class, 'id_jenis_kayu', 'id_jenis_kayu');
    }

}
