<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_newsletter')) {
            Schema::create('table_newsletter', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('ten')->nullable();\n                $table->string('taptin')->nullable();\n                $table->string('email')->nullable();\n                $table->text('dienthoai')->nullable();\n                $table->string('chude')->nullable();\n                $table->text('noidung')->nullable();\n                $table->string('ghichu')->nullable();\n                $table->string('tinhtrang')->nullable();\n                $table->text('diachi')->nullable();\n                $table->string('soluong')->nullable();\n                $table->string('thoigian')->nullable();\n                $table->string('type')->nullable();\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->integer('ngaytao')->nullable()->default(0);\n                $table->integer('ngaysua')->nullable()->default(0);\n                $table->integer('stt')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_newsletter');
    }
};
