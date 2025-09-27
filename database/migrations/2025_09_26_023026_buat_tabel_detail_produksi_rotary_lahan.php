<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('produksi_rotary_lahans', function (Blueprint $table) {
            $table->id('id_rotary_lahan');
            $table->unsignedBigInteger('id_target');
            //relasi dengan tabel produksi rotary
            $table->unsignedBigInteger('id_produksi_rotary');
            $table->foreign('id_produksi_rotary')
                ->references('id_produksi_rotary') // sesuaikan dengan PK di tabel targets
                ->on('produksi_rotaries')
                ->onDelete('restrict');
            //relasi dengan tebal lahan
            $table->unsignedBigInteger('id_lahan');
            $table->foreign('id_lahan')
                ->references('id_lahan') // sesuaikan dengan PK di tabel targets
                ->on('lahans')
                ->onDelete('restrict');

            $table->integer('jumlah_batang')->default(0);
            $table->decimal('kubikasi', 12, 4)->default(0);
            $table->integer('hasilkw1')->default(0);
            $table->integer('hasilkw2')->default(0);
            $table->integer('hasilkw3')->default(0);
            $table->integer('hasilkw4')->default(0);
            //kolom total menjumlahkan otomatis via database hasilkw1 + hasilkw2 + hasilkw3 + hasilkw4
            $table->integer('total')->storedAs('hasilkw1 + hasilkw2 + hasilkw3 + hasilkw4');


            $table->integer('target_produksi')->default(0);
            $table->integer('capaian_produksi')->default(0);
            $table->integer('potongan_target')->default(0);
            $table->timestamps();
            // Foreign key ke tabel targets
            $table->foreign('id_target')
                ->references('id_target') // sesuaikan dengan PK di tabel targets
                ->on('targets')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('produksi_rotary_lahan');
    }
};
