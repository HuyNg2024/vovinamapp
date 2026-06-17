<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_seo')) {
            Schema::create('table_seo', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('idmuc')->nullable()->default(0);\n                $table->string('com')->nullable();\n                $table->string('act')->nullable();\n                $table->string('type')->nullable();\n                $table->text('sloganvi')->nullable();\n                $table->text('sloganen')->nullable();\n                $table->text('titlevi')->nullable();\n                $table->text('keywordsvi')->nullable();\n                $table->text('descriptionvi')->nullable();\n                $table->text('titleen')->nullable();\n                $table->text('keywordsen')->nullable();\n                $table->text('descriptionen')->nullable();\n                $table->text('titleko')->nullable();\n                $table->text('keywordsko')->nullable();\n                $table->text('descriptionko')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_seo');
    }
};
