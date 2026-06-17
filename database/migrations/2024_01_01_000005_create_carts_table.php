<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->bigInteger('member_id');\n                $table->bigInteger('product_id');\n                $table->integer('quantity');\n                $table->timestamp('created_at')->nullable();\n                $table->timestamp('updated_at')->nullable();\n                $table->string('total_price')->default('0.00');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
