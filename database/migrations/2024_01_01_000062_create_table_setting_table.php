<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_setting')) {
            Schema::create('table_setting', function (Blueprint $table) {
                $table->integer('id');\n                $table->text('options')->nullable();\n                $table->text('mastertool')->nullable();\n                $table->text('headjs')->nullable();\n                $table->text('bodyjs')->nullable();\n                $table->string('tenvi')->nullable();\n                $table->string('tenen')->nullable();\n                $table->string('tenko')->nullable();\n                $table->text('analytics')->nullable();\n                $table->string('sloganvi')->nullable();\n                $table->string('sloganen')->nullable();\n                $table->text('titlevi')->nullable();\n                $table->text('keywordsvi')->nullable();\n                $table->text('descriptionvi')->nullable();\n                $table->text('titleen')->nullable();\n                $table->text('keywordsen')->nullable();\n                $table->text('descriptionen')->nullable();\n                $table->text('titleko')->nullable();\n                $table->text('keywordsko')->nullable();\n                $table->text('descriptionko')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_setting');
    }
};
