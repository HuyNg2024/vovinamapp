<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToProductsTable extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('CategoryID')->primary();
            $table->string('CategoryName');
            //$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
