<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_excel')) {
            Schema::create('table_excel', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('photo')->nullable();\n                $table->string('type')->nullable();\n                $table->integer('stt')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_excel');
    }
};
