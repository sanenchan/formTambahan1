<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class KategoriMesin extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_kategori_mesin';

    protected $fillable = [
        'nama_kategori', // jika sebelumnya sudah ditambahkan
    ];

    // Relasi ke Mesin
    public function mesins()
    {
        return $this->hasMany(Mesin::class, 'id_kategori_mesin');
    }
}
