<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->integer('ProductID');\n                $table->string('ProductName');\n                $table->integer('SupplierID');\n                $table->text('UnitPrice');\n                $table->smallInteger('UnitsInStock');\n                $table->string('CategoryID')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
