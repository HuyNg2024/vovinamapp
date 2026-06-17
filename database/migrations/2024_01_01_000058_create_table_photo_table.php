<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_photo')) {
            Schema::create('table_photo', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('id_city')->default(0);\n                $table->integer('id_district')->default(0);\n                $table->integer('noibat')->nullable()->default(0);\n                $table->string('photo')->nullable();\n                $table->text('noidungen')->nullable();\n                $table->text('noidungvi')->nullable();\n                $table->text('motaen')->nullable();\n                $table->text('motavi')->nullable();\n                $table->string('tenen')->nullable();\n                $table->string('tenvi')->nullable();\n                $table->string('tenko')->nullable();\n                $table->text('motako')->nullable();\n                $table->text('noidungko')->nullable();\n                $table->text('link')->nullable();\n                $table->text('link_register')->nullable();\n                $table->text('link_video')->nullable();\n                $table->string('file_video');\n                $table->text('diachivi')->nullable();\n                $table->text('diachien')->nullable();\n                $table->string('dienthoai')->nullable();\n                $table->string('email')->nullable();\n                $table->string('thoigian')->nullable();\n                $table->string('taptin');\n                $table->text('iframe')->nullable();\n                $table->text('options')->nullable();\n                $table->string('type')->nullable();\n                $table->string('act')->nullable();\n                $table->integer('stt')->nullable()->default(0);\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->integer('ngaytao')->nullable()->default(0);\n                $table->integer('ngaysua')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_photo');
    }
};
