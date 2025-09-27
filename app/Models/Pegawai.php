<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // Nama tabel (opsional, karena default akan plural dari nama model -> "pegawais")
    protected $table = 'pegawais';

    // Primary key (opsional, default 'id')
    protected $primaryKey = 'id_pegawai';

    // Kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'kode_pegawai',
        'nama_pegawai',
        'alamat',
        'no_telepon_pegawai',
        'jenis_kelamin_pegawai',
        'tanggal_masuk',
    ];

    // Cast agar tipe data sesuai
    protected $casts = [
        'jenis_kelamin_pegawai' => 'boolean',
        'tanggal_masuk' => 'date',
    ];

    public function detailProduksiRotaries()
    {
        return $this->hasMany(DetailProduksiRotary::class, 'id_pegawai', 'id');
    }
}
