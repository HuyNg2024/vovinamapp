<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_gallery')) {
            Schema::create('table_gallery', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('id_photo')->nullable()->default(0);\n                $table->string('photo')->nullable();\n                $table->string('hash')->nullable();\n                $table->string('tenen')->nullable();\n                $table->string('tenvi')->nullable();\n                $table->string('mau')->nullable();\n                $table->integer('id_mau')->nullable()->default(0);\n                $table->integer('id_size')->nullable()->default(0);\n                $table->string('taptin')->nullable();\n                $table->text('link')->nullable();\n                $table->text('link_video')->nullable();\n                $table->double('gia')->default(0);\n                $table->integer('stt')->nullable()->default(0);\n                $table->string('type')->nullable();\n                $table->string('com')->nullable();\n                $table->string('kind')->nullable();\n                $table->string('val')->nullable();\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->integer('ngaytao')->nullable()->default(0);\n                $table->integer('ngaysua')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_gallery');
    }
};
