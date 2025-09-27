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
        Schema::create('produksi_rotary_pegawais', function (Blueprint $table) {
            $table->bigIncrements('id_produksi_rotary_pegawai');

            $table->unsignedBigInteger('id_produksi_rotary');
            $table->foreign('id_produksi_rotary')
                ->references('id_produksi_rotary') // sesuaikan dengan PK di tabel targets
                ->on('produksi_rotaries')
                ->onDelete('restrict');

            $table->unsignedBigInteger('id_pegawai');
            $table->foreign('id_pegawai')
                ->references('id_pegawai')
                ->on('pegawais')
                ->onDelete('restrict');

            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('potongan_target')->default(0);
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotary_lahan_pegawai');
        //
    }
};
