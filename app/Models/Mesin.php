<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Mesin extends Model
{
    use HasFactory;

    // Jika primary key bukan "id", sesuaikan
    protected $primaryKey = 'id_mesin';

    // Mass assignable
    protected $fillable = [
        'nama_mesin',
        'ongkos_mesin',
        'no_akun',
        'detail_mesin',
        'id_kategori_mesin', // nanti untuk relasi dropdown kategori
    ];

    // Relasi ke kategori mesin
    public function kategori()
    {
        return $this->belongsTo(KategoriMesin::class, 'id_kategori_mesin');
    }
    public function targets()
    {
        return $this->hasMany(Target::class, 'id_mesin', 'id_mesin');
    }
}
