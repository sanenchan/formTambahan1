<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $table = 'targets';
    protected $primaryKey = 'id_target';

    protected $fillable = [
        'id_mesin',
        'id_ukuran',
        'id_jenis_kayu',
        'ukuran',
        'target',
        'orang',
        'jam',
        'gaji',
        'status',
    ];

    protected $casts = [
        'target' => 'integer',
        'orang' => 'integer',
        'jam' => 'integer',
        'targetperjam' => 'decimal:2',
        'targetperorang' => 'decimal:2',
        'gaji' => 'decimal:2',
        'potongan' => 'decimal:2',
    ];
    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'id_mesin', 'id_mesin');
    }

    public function ukuranModel()
    {
        return $this->belongsTo(Ukuran::class, 'id_ukuran', 'id_ukuran');
    }

    public function jenisKayu()
    {
        return $this->belongsTo(JenisKayu::class, 'id_jenis_kayu', 'id_jenis_kayu');
    }
    public function produksiRotaries()
    {
        return $this->hasMany(ProduksiRotary::class, 'id_target', 'id_target');
    }

}
