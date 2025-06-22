<?php
// database/migrations/xxxx_xx_xx_create_detail_pembelian_merchandise_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailPembelianMerchandiseTable extends Migration
{
    public function up()
    {
        Schema::create('detail_pembelian_merchandise', function (Blueprint $table) {
            $table->id('id_detail_pembelianMerch');
            $table->unsignedBigInteger('id_pembelianMerch');
            $table->unsignedBigInteger('id_merchandise');
            $table->integer('jumlah');
            $table->decimal('harga', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->foreign('id_pembelianMerch')->references('id_pembelianMerch')->on('pembelian_merchandise')->onDelete('cascade');
            $table->foreign('id_merchandise')->references('id_merchandise')->on('merchandise')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_pembelian_merchandise');
    }
}
