<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_courses_cat')) {
            Schema::create('table_courses_cat', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('id_list');\n                $table->boolean('noibat')->nullable()->default(0);\n                $table->boolean('tb')->nullable()->default(0);\n                $table->string('tenkhongdauvi')->nullable();\n                $table->string('tenkhongdauen')->nullable();\n                $table->text('noidungen')->nullable();\n                $table->text('noidungvi')->nullable();\n                $table->text('motaen')->nullable();\n                $table->text('motavi')->nullable();\n                $table->string('tenko');\n                $table->string('tenkhongdauko');\n                $table->text('motako');\n                $table->text('noidungko');\n                $table->string('tenen')->nullable();\n                $table->string('tenvi')->nullable();\n                $table->string('photo')->nullable();\n                $table->string('icon')->nullable();\n                $table->text('options')->nullable();\n                $table->integer('stt')->nullable()->default(0);\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->string('type')->nullable();\n                $table->integer('ngaytao')->nullable()->default(0);\n                $table->integer('ngaysua')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_courses_cat');
    }
};
