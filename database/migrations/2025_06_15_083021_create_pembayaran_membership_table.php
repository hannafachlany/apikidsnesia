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
        Schema::create('pembayaran_membership', function (Blueprint $table) {
            $table->id('id_pembayaranMembership'); // BIGINT auto-increment
            $table->unsignedBigInteger('id_membership');
            $table->string('bank_pengirim', 50);
            $table->dateTime('waktu_transfer')->nullable(); // opsional, tergantung kebutuhan
            $table->integer('jumlah_transfer');
            $table->string('status_pembayaran', 20)->default('Pending');
            $table->timestamps();

            $table->foreign('id_membership')->references('id_membership')->on('membership')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembayaran_membership');
    }
};
