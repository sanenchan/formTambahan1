<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiRotary extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_produksi_rotary';

    protected $fillable = [
        'tanggal_produksi',
        'jam_mulai_mesin',
        'jam_selesai_mesin',
        'kendala',
        'status_data',
        'pekerja',
        'capaian_produksi',
        'target_produksi',
        'status_produksi',
        'potongan_target',
        'kubikasi',
        'jam_kerja_mulai',
        'jam_kerja_selesai',
    ];

    protected $casts = [
        'tanggal_produksi' => 'date',
        'jam_mulai_mesin' => 'datetime',
        'jam_selesai_mesin' => 'datetime',
        'jam_kerja_mulai' => 'datetime',
        'jam_kerja_selesai' => 'datetime',
        'pekerja' => 'integer',
        'capaian_produksi' => 'decimal:2',
        'target_produksi' => 'decimal:2',
        'status_produksi' => 'decimal:2',
        'potongan_target' => 'decimal:2',
        'kubikasi' => 'decimal:3',
    ];

    // Relasi ke pegawai produksi
    public function pegawaiProduksi()
    {
        return $this->hasMany(RotaryLahanPegawai::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }

    // Relasi ke lahan produksi
    public function lahanProduksi()
    {
        return $this->hasMany(ProduksiRotaryLahan::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }

    protected static function booted()
    {
        // Event ketika data disimpan
        static::saved(function ($produksi) {
            // Update jumlah pekerja berdasarkan pegawai yang terdaftar
            $jumlahPekerja = $produksi->pegawaiProduksi()->count();
            if ($produksi->pekerja != $jumlahPekerja) {
                $produksi->updateQuietly(['pekerja' => (float) $jumlahPekerja]);
            }

            // Distribusi potongan target per pekerja jika ada
            if ($jumlahPekerja > 0 && $produksi->potongan_target > 0) {
                $potonganPerOrang = (float) ($produksi->potongan_target / $jumlahPekerja);

                $produksi->pegawaiProduksi()->update([
                    'potongan_target' => $potonganPerOrang,
                ]);
            }
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Hitung total capaian produksi dari semua lahan
            $totalCapaian = (float) $model->lahanProduksi->sum(function ($lahan) {
                return ($lahan->hasilkw1 ?? 0) +
                    ($lahan->hasilkw2 ?? 0) +
                    ($lahan->hasilkw3 ?? 0) +
                    ($lahan->hasilkw4 ?? 0);
            });

            $model->capaian_produksi = $totalCapaian;

            // Hitung total kubikasi dari semua lahan
            $model->kubikasi = (float) $model->lahanProduksi->sum('kubikasi');

            // Hitung target produksi berdasarkan durasi kerja dan jumlah pekerja
            $model->calculateTargetProduksi();

            // Hitung status produksi dan potongan target
            $model->status_produksi = (float) ($model->capaian_produksi - ($model->target_produksi ?? 0));

            // Potongan target dihitung jika tidak mencapai target
            $model->potongan_target = ($model->status_produksi < 0)
                ? (float) (abs($model->status_produksi) * $model->getPotonganRate())
                : (float) 0;
        });
    }

    /**
     * Hitung target produksi berdasarkan durasi kerja dan jumlah pekerja
     */
    protected function calculateTargetProduksi()
    {
        // Gunakan variabel jam_kerja untuk perhitungan, bukan jam_mulai_mesin
        if (empty($this->jam_kerja_mulai) || empty($this->jam_kerja_selesai)) {
            $this->target_produksi = (float) 0;
            return;
        }

        $durasiJam = $this->getDurasiKerjaJam();
        $jumlahPekerja = (float) ($this->pekerja ?? 0);

        // Ambil target dari lahan pertama (asumsi semua lahan menggunakan target yang sama)
        $targetData = $this->lahanProduksi->first()?->target;

        if (!$targetData) {
            $this->target_produksi = (float) 0;
            return;
        }

        $targetTotal = (float) ($targetData->target ?? 0);
        $targetOrang = (float) ($targetData->orang ?? 1);
        $targetJam = (float) ($targetData->jam ?? 1);

        $targetPerOrangPerJam = ($targetOrang > 0 && $targetJam > 0)
            ? $targetTotal / $targetOrang / $targetJam
            : 0;

        $this->target_produksi = (float) round($targetPerOrangPerJam * $jumlahPekerja * $durasiJam, 2);
    }

    /**
     * Hitung durasi kerja dalam jam menggunakan jam_kerja_mulai dan jam_kerja_selesai
     */
    protected function getDurasiKerjaJam()
    {
        if (empty($this->jam_kerja_mulai) || empty($this->jam_kerja_selesai)) {
            return 0;
        }

        $start = strtotime($this->jam_kerja_mulai);
        $end = strtotime($this->jam_kerja_selesai);

        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }

        return round(($end - $start) / 3600, 2);
    }

    /**
     * Accessor untuk mendapatkan durasi mesin dalam jam
     */
    public function getDurasiMesinJamAttribute()
    {
        if (empty($this->jam_mulai_mesin) || empty($this->jam_selesai_mesin)) {
            return 0;
        }

        $start = strtotime($this->jam_mulai_mesin);
        $end = strtotime($this->jam_selesai_mesin);

        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }

        return round(($end - $start) / 3600, 2);
    }

    /**
     * Dapatkan rate potongan
     */
    protected function getPotonganRate()
    {
        $targetData = $this->lahanProduksi->first()?->target;
        return (float) ($targetData->potongan ?? 0);
    }

    /**
     * Scope untuk data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status_data', 1);
    }

    /**
     * Accessor untuk efisiensi produksi
     */
    public function getEfisiensiProduksiAttribute()
    {
        if (!$this->target_produksi || $this->target_produksi == 0) {
            return 0;
        }

        return round(($this->capaian_produksi / $this->target_produksi) * 100, 2);
    }
}