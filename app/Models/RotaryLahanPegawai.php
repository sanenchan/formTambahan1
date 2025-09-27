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
        'jam_kerja_mulai',
        'jam_kerja_selesai',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
        'jam_kerja_mulai' => 'datetime',
        'jam_kerja_selesai' => 'datetime',
        'potongan_target' => 'decimal:2',
    ];

    // Relasi ke pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    // Relasi ke produksi rotary (parent)
    public function produksiRotary()
    {
        return $this->belongsTo(ProduksiRotary::class, 'id_produksi_rotary', 'id_produksi_rotary');
    }

    protected static function boot()
    {
        parent::boot();

        // Validasi sebelum menyimpan
        static::saving(function ($model) {
            // Validasi jam mesin
            if ($model->jam_mulai && $model->jam_selesai) {
                $jamMulai = strtotime($model->jam_mulai);
                $jamSelesai = strtotime($model->jam_selesai);

                if ($jamSelesai <= $jamMulai) {
                    throw new \InvalidArgumentException('Jam selesai harus lebih besar dari jam mulai');
                }
            }

            // Validasi jam kerja untuk perhitungan
            if ($model->jam_kerja_mulai && $model->jam_kerja_selesai) {
                $jamKerjaMulai = strtotime($model->jam_kerja_mulai);
                $jamKerjaSelesai = strtotime($model->jam_kerja_selesai);

                if ($jamKerjaSelesai <= $jamKerjaMulai) {
                    throw new \InvalidArgumentException('Jam kerja selesai harus lebih besar dari jam kerja mulai');
                }
            }

            // Cek duplikat pegawai dalam produksi yang sama
            $existing = static::where('id_produksi_rotary', $model->id_produksi_rotary)
                ->where('id_pegawai', $model->id_pegawai)
                ->where($model->primaryKey, '!=', $model->getKey())
                ->exists();

            if ($existing) {
                throw new \InvalidArgumentException('Pegawai sudah terdaftar dalam produksi rotary ini');
            }
        });
    }

    protected static function booted()
    {
        // Update jumlah pekerja di produksi rotary setelah data disimpan
        static::saved(function ($pegawaiProduksi) {
            $pegawaiProduksi->updateProduksiRotaryPekerja();
        });

        // Update jumlah pekerja di produksi rotary setelah data dihapus
        static::deleted(function ($pegawaiProduksi) {
            if ($pegawaiProduksi->produksiRotary) {
                $pegawaiProduksi->updateProduksiRotaryPekerja();
            }
        });
    }

    /**
     * Update jumlah pekerja di produksi rotary
     */
    protected function updateProduksiRotaryPekerja()
    {
        if (!$this->produksiRotary)
            return;

        $jumlahPekerja = $this->produksiRotary->pegawaiProduksi->count();

        $this->produksiRotary->updateQuietly([
            'pekerja' => (float) $jumlahPekerja
        ]);

        // Redistribut potongan target jika ada
        $this->redistributePotonganTarget();
    }

    /**
     * Redistribut potongan target ke semua pekerja
     */
    protected function redistributePotonganTarget()
    {
        $produksiRotary = $this->produksiRotary;
        $totalPotonganTarget = (float) ($produksiRotary->potongan_target ?? 0);
        $jumlahPekerja = (float) ($produksiRotary->pekerja ?? 0);

        if ($jumlahPekerja > 0 && $totalPotonganTarget > 0) {
            $potonganPerOrang = (float) round($totalPotonganTarget / $jumlahPekerja, 2);

            // Update semua pegawai produksi dengan potongan yang sama
            $produksiRotary->pegawaiProduksi->each(function ($pegawai) use ($potonganPerOrang) {
                $pegawai->updateQuietly(['potongan_target' => $potonganPerOrang]);
            });
        }
    }

    /**
     * Hitung durasi kerja pegawai dalam jam (menggunakan jam_kerja)
     */
    public function getDurasiKerjaJamAttribute()
    {
        if (!$this->jam_kerja_mulai || !$this->jam_kerja_selesai) {
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
     * Hitung durasi kehadiran pegawai dalam jam (menggunakan jam_mulai/jam_selesai)
     */
    public function getDurasiKehadiranJamAttribute()
    {
        if (!$this->jam_mulai || !$this->jam_selesai) {
            return 0;
        }

        $start = strtotime($this->jam_mulai);
        $end = strtotime($this->jam_selesai);

        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }

        return round(($end - $start) / 3600, 2);
    }

    /**
     * Scope untuk pegawai aktif dalam periode tertentu
     */
    public function scopeAktifPada($query, $tanggal)
    {
        return $query->whereHas('produksiRotary', function ($q) use ($tanggal) {
            $q->where('tanggal_produksi', $tanggal);
        });
    }

    /**
     * Scope untuk pegawai dengan durasi kerja minimum
     */
    public function scopeMinimalDurasiJam($query, $minJam = 1)
    {
        return $query->whereRaw("TIME_TO_SEC(TIMEDIFF(jam_selesai, jam_mulai)) >= ?", [$minJam * 3600]);
    }

    /**
     * Accessor untuk informasi lengkap pegawai
     */
    public function getPegawaiInfoAttribute()
    {
        return [
            'nama' => $this->pegawai->nama ?? 'Tidak Diketahui',
            'durasi_kerja_jam' => $this->durasi_kerja_jam,
            'durasi_kehadiran_jam' => $this->durasi_kehadiran_jam,
            'potongan_target' => $this->potongan_target ?? 0,
        ];
    }
}