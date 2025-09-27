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
        Schema::create('mesins', function (Blueprint $table) {
            $table->bigIncrements('id_mesin');
            $table->unsignedBigInteger('id_kategori_mesin');
            $table->string('nama_mesin');
            $table->decimal('ongkos_mesin', 15, 2); // jumlah uang
            $table->string('no_akun');
            $table->text('detail_mesin')->nullable();
            $table->timestamps();

            //relasi dengan tabel Kategori Mesin
            $table->foreign('id_kategori_mesin')
                ->references('id_kategori_mesin')
                ->on('kategori_mesins')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('mesins');
        Schema::table('mesins', function (Blueprint $table) {
            $table->dropForeign(['id_kategori_mesin']);
            $table->dropColumn('id_kategori_mesin');
        });
    }
};
