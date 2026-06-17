<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_ordercourse_detail')) {
            Schema::create('table_ordercourse_detail', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('id_course')->nullable()->default(0);\n                $table->integer('id_order')->nullable()->default(0);\n                $table->string('photo')->nullable();\n                $table->string('ten')->nullable();\n                $table->string('code')->nullable();\n                $table->string('mau')->nullable();\n                $table->string('size')->nullable();\n                $table->double('gia')->nullable()->default(0);\n                $table->double('giamoi')->nullable()->default(0);\n                $table->integer('soluong')->nullable()->default(0);\n                $table->string('donvi');\n                $table->string('ghichu');\n                $table->integer('ngaytao');\n                $table->tinyInteger('paid')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_ordercourse_detail');
    }
};
