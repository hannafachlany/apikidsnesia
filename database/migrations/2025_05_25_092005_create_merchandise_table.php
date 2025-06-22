<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandiseTable extends Migration
{
    public function up()
    {
        Schema::create('merchandise', function (Blueprint $table) {
            $table->id('id_merchandise');
            $table->string('nama_merchandise');
            $table->integer('harga_merchandise');
            $table->string('foto_merchandise');
            $table->text('deskripsi_merchandise');
            $table->integer('stok');
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchandise');
    }
}
