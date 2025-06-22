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
    Schema::create('pelanggan', function (Blueprint $table) {
        $table->id('id_pelanggan');
        $table->string('email')->unique();
        $table->string('password');
        $table->string('nama_pelanggan');
        $table->string('no_hp_pelanggan');
        $table->string('token')->nullable();
        $table->timestamp('token_expired_at')->nullable();
    });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelanggans');
    }
};
