<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    protected $table = 'lahans';

    // Primary key (opsional, default 'id')
    protected $primaryKey = 'id_lahan';

    // Kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'kode_lahan',
        'nama_lahan',
    ];
    public function stokKayus()
    {
        return $this->hasMany(\App\Models\StokKayu::class, 'id_lahan', 'id_lahan');
    }
    public function produksiRotaries()
    {
        return $this->hasMany(ProduksiRotary::class, 'id_lahan', 'id_lahan');
    }
    public function produksiRotaryLahans()
    {
        return $this->hasMany(ProduksiRotaryLahan::class, 'id_lahan', 'id_lahan');
    }

}


