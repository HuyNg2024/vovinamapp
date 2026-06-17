<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_permission')) {
            Schema::create('table_permission', function (Blueprint $table) {
                $table->integer('ma');\n                $table->integer('ma_nhom_quyen')->nullable()->default(0);\n                $table->string('quyen')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_permission');
    }
};
