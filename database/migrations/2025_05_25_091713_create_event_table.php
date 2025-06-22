<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTable extends Migration
{
    public function up()
    {
        Schema::create('event', function (Blueprint $table) {
            $table->id('id_event');
            $table->string('nama_event');
            $table->integer('harga_event');
            $table->date('jadwal_event');
            $table->string('foto_event');
            $table->text('deskripsi_event');
            $table->integer('kuota');
        });
    }

    public function down()
    {
        Schema::dropIfExists('event');
    }
}
