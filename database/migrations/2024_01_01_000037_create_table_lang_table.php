<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_lang')) {
            Schema::create('table_lang', function (Blueprint $table) {
                $table->integer('id');\n                $table->text('giatri')->nullable();\n                $table->text('langvi')->nullable();\n                $table->text('langen')->nullable();\n                $table->text('langzh')->nullable();\n                $table->text('langko');\n                $table->integer('stt')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_lang');
    }
};
