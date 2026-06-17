<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_coupon')) {
            Schema::create('table_coupon', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('ma')->nullable();\n                $table->integer('stt')->nullable()->default(0);\n                $table->integer('loai')->nullable()->default(0);\n                $table->integer('tinhtrang')->nullable()->default(0);\n                $table->integer('chietkhau')->nullable()->default(0);\n                $table->integer('ngaybatdau')->nullable()->default(0);\n                $table->integer('ngayketthuc')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_coupon');
    }
};
