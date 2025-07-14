<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('ProductID'); // Tạo cột ProductID với kiểu dữ liệu unsignedBigInteger
            $table->string('ProductName');
            $table->foreignId('SupplierID')->constrained('suppliers', 'SupplierID');
            $table->decimal('UnitPrice', 10, 2);
            $table->integer('UnitsInStock');
           // $table->timestamps(); // Thêm dòng này để có cột created_at và updated_at
           
           $table->string('CategoryID')->nullable();
            // Đảm bảo bảng `categories` đã tồn tại trước khi thêm khóa ngoại
            $table->foreign('CategoryID')->references('CategoryID')->on('categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['CategoryID']);
            $table->dropColumn('CategoryID');
        });
    }
}

