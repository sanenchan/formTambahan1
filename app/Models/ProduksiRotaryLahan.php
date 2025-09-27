<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiRotaryLahan extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_rotary_lahan';

    protected $fillable = [
        'id_target',
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

    protected $casts = [
        'jumlah_batang' => 'integer',
        'kubikasi' => 'decimal:3',
        'hasilkw1' => 'decimal:2',
        'hasilkw2' => 'decimal:2',
        'hasilkw3' => 'decimal:2',
        'hasilkw4' => 'decimal:2',
        'target_produksi' => 'decimal:2',
        'status_produksi' => 'decimal:2',
        'capaian_produksi' => 'decimal:2',
        'potongan_target' => 'decimal:2',
    ];

    // Relasi ke produksi rotary (parent)
    public function produksiRotary()
    {
        return $this->belongsTo(ProduksiRotary::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }

    // Relasi ke target
    public function target()
    {
        return $this->belongsTo(Target::class, 'id_target', 'id_target');
    }

    // Relasi ke lahan
    public function lahan()
    {
        return $this->belongsTo(Lahan::class, 'id_lahan', 'id_lahan');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Pastikan relasi target dan ukuran model termuat
            $model->loadMissing('target.ukuranModel');

            // Hitung capaian produksi
            $model->capaian_produksi = ($model->hasilkw1 ?? 0)
                + ($model->hasilkw2 ?? 0)
                + ($model->hasilkw3 ?? 0)
                + ($model->hasilkw4 ?? 0);

            // Hitung kubikasi berdasarkan ukuran dan capaian
            $model->calculateKubikasi();

            // Hitung target produksi untuk lahan ini
            $model->calculateTargetProduksi();

            // Hitung status dan potongan target
            $model->status_produksi = $model->capaian_produksi - ($model->target_produksi ?? 0);
            $model->potongan_target = ($model->status_produksi < 0 && $model->target)
                ? abs($model->status_produksi) * ($model->target->potongan ?? 0)
                : 0;
        });
    }

    protected static function booted()
    {
        // Update produksi rotary setelah lahan disimpan
        static::saved(function ($lahan) {
            $lahan->updateProduksiRotaryTotals();
        });

        // Update produksi rotary setelah lahan dihapus
        static::deleted(function ($lahan) {
            if ($lahan->produksiRotary) {
                $lahan->updateProduksiRotaryTotals();
            }
        });
    }

    /**
     * Hitung kubikasi berdasarkan ukuran model dan capaian produksi
     */
    protected function calculateKubikasi()
    {
        $ukuran = $this->target?->ukuranModel;

        if (!$ukuran) {
            $this->kubikasi = (float) 0;
            return;
        }

        $panjang = (float) ($ukuran->panjang ?? 0);
        $tinggi = (float) ($ukuran->tinggi ?? 0);
        $tebal = (float) ($ukuran->tebal ?? 0);

        if ($panjang > 0 && $tinggi > 0 && $tebal > 0) {
            $this->kubikasi = (float) round(
                ($panjang * $tinggi * $tebal * $this->capaian_produksi) / 1000000,
                3
            );
        } else {
            $this->kubikasi = (float) 0;
        }
    }

    /**
     * Hitung target produksi untuk lahan ini berdasarkan proporsi
     */
    protected function calculateTargetProduksi()
    {
        if (!$this->produksiRotary || !$this->target) {
            $this->target_produksi = (float) 0;
            return;
        }

        // Gunakan method dari parent untuk mendapatkan durasi kerja efektif
        $durasiJam = $this->produksiRotary->getDurasiKerjaJam();
        $jumlahPekerja = (float) ($this->produksiRotary->pekerja ?? 0);

        if ($durasiJam <= 0 || $jumlahPekerja <= 0) {
            $this->target_produksi = (float) 0;
            return;
        }

        $targetTotal = (float) ($this->target->target ?? 0);
        $targetOrang = (float) ($this->target->orang ?? 1);
        $targetJam = (float) ($this->target->jam ?? 1);

        $targetPerOrangPerJam = ($targetOrang > 0 && $targetJam > 0)
            ? $targetTotal / $targetOrang / $targetJam
            : 0;

        // Hitung proporsi lahan ini terhadap total lahan (berdasarkan jumlah batang atau area)
        $totalLahan = $this->produksiRotary->lahanProduksi->count();
        $proporsiLahan = $totalLahan > 0 ? 1 / $totalLahan : 1;

        $this->target_produksi = (float) round(
            $targetPerOrangPerJam * $jumlahPekerja * $durasiJam * $proporsiLahan,
            2
        );
    }

    /**
     * Update total-total di produksi rotary
     */
    protected function updateProduksiRotaryTotals()
    {
        if (!$this->produksiRotary)
            return;

        $produksiRotary = $this->produksiRotary;

        // Hitung ulang total capaian dari semua lahan
        $totalCapaian = $produksiRotary->lahanProduksi->sum(function ($item) {
            return ($item->hasilkw1 ?? 0)
                + ($item->hasilkw2 ?? 0)
                + ($item->hasilkw3 ?? 0)
                + ($item->hasilkw4 ?? 0);
        });

        // Hitung ulang total kubikasi
        $totalKubikasi = $produksiRotary->lahanProduksi->sum('kubikasi');

        // Hitung ulang total target produksi
        $totalTargetProduksi = $produksiRotary->lahanProduksi->sum('target_produksi');

        // Update produksi rotary tanpa trigger event
        $produksiRotary->updateQuietly([
            'capaian_produksi' => (float) $totalCapaian,
            'kubikasi' => (float) $totalKubikasi,
            'target_produksi' => (float) $totalTargetProduksi,
            'status_produksi' => (float) ($totalCapaian - $totalTargetProduksi),
        ]);
    }

    /**
     * Accessor untuk efisiensi produksi lahan
     */
    public function getEfisiensiProduksiAttribute()
    {
        if (!$this->target_produksi || $this->target_produksi == 0) {
            return 0;
        }

        return round(($this->capaian_produksi / $this->target_produksi) * 100, 2);
    }

    /**
     * Accessor untuk total hasil KW
     */
    public function getTotalHasilKwAttribute()
    {
        return ($this->hasilkw1 ?? 0) +
            ($this->hasilkw2 ?? 0) +
            ($this->hasilkw3 ?? 0) +
            ($this->hasilkw4 ?? 0);
    }
}