<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id(); // Tự động tạo cột id là khóa chính
            $table->unsignedBigInteger('order_id'); // ID của đơn hàng
            $table->unsignedBigInteger('product_id'); // ID của sản phẩm
            $table->integer('quantity'); // Số lượng sản phẩm
            $table->decimal('price', 10, 2); // Giá của sản phẩm

            $table->timestamps(); // Tự động tạo cột created_at và updated_at

            // Khóa ngoại (nếu cần, nhưng như bạn yêu cầu, không sử dụng khóa ngoại)
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}
