<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_event', function (Blueprint $table) {
            $table->id('id_detail_event');
            $table->unsignedBigInteger('id_event');
            $table->string('foto_kegiatan'); // simpan nama file foto
            $table->timestamps();

            $table->foreign('id_event')->references('id_event')->on('event')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_event');
    }
};
