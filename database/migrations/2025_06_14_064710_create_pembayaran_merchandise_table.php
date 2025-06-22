<?php
// database/migrations/xxxx_xx_xx_create_pembayaran_merchandise_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaranMerchandiseTable extends Migration
{
    public function up()
    {
        Schema::create('pembayaran_merchandise', function (Blueprint $table) {
            $table->id('id_pembayaranMerch');
            $table->unsignedBigInteger('id_pembelianMerch');
            $table->string('bank');
            $table->decimal('total_harga', 10, 2);
            $table->timestamp('tanggal_bayar')->nullable();
            $table->enum('status_pembayaran', ['Pending', 'Berhasil', 'Gagal'])->default('Pending');
            $table->timestamps();

            $table->foreign('id_pembelianMerch')->references('id_pembelianMerch')->on('pembelian_merchandise')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran_merchandise');
    }
}
