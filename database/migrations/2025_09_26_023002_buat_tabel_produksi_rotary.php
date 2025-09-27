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
        Schema::create('produksi_rotaries', function (Blueprint $table) {
            $table->id('id_produksi_rotary');
            $table->date('tanggal_produksi');
            $table->time('jam_mulai_mesin')->nullable();
            $table->time('jam_selesai_mesin')->nullable();
            $table->text('kendala')->nullable();
            $table->integer('status_data')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_rotaries');
    }
};
