<?php
// database/migrations/xxxx_xx_xx_create_pembelian_merchandise_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembelianMerchandiseTable extends Migration
{
    public function up()
    {
        Schema::create('pembelian_merchandise', function (Blueprint $table) {
            $table->id('id_pembelianMerch');
            $table->unsignedBigInteger('id_pelanggan');
            $table->decimal('total_pembelian', 10, 2);
            $table->timestamp('tanggal_pembelian')->useCurrent();
            $table->enum('status_pembelian', ['Pending', 'Berhasil', 'Gagal'])->default('Pending');
            $table->boolean('is_checkout')->default(false);

            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelian_merchandise');
    }
}
