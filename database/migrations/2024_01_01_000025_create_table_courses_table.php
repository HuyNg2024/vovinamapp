<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_courses')) {
            Schema::create('table_courses', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('id_teachers');\n                $table->integer('id_list')->nullable()->default(0);\n                $table->integer('id_item')->nullable()->default(0);\n                $table->integer('id_cat')->nullable()->default(0);\n                $table->integer('id_sub')->nullable()->default(0);\n                $table->string('id_tags')->nullable();\n                $table->boolean('noibat')->nullable()->default(0);\n                $table->boolean('tb')->nullable()->default(0);\n                $table->string('photo')->nullable();\n                $table->string('icon')->nullable();\n                $table->text('options')->nullable();\n                $table->string('tenkhongdauvi')->nullable();\n                $table->string('tenkhongdauen')->nullable();\n                $table->text('noidungen')->nullable();\n                $table->text('noidungvi')->nullable();\n                $table->text('motaen')->nullable();\n                $table->text('motavi')->nullable();\n                $table->text('gioithieuvi');\n                $table->string('tenen')->nullable();\n                $table->string('tenvi')->nullable();\n                $table->string('tenko');\n                $table->string('tenkhongdauko');\n                $table->text('motako');\n                $table->text('noidungko');\n                $table->string('taptin')->nullable();\n                $table->text('link')->nullable();\n                $table->text('link_video')->nullable();\n                $table->string('thoigian')->nullable();\n                $table->double('giamoi');\n                $table->double('giatien');\n                $table->text('lichhocvi');\n                $table->string('ghichuvi');\n                $table->integer('stt')->nullable()->default(0);\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->string('type')->nullable()->default('0');\n                $table->integer('ngaytao')->nullable()->default(0);\n                $table->integer('ngaysua')->nullable()->default(0);\n                $table->integer('luotxem')->nullable()->default(0);\n                $table->integer('id_club');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_courses');
    }
};
