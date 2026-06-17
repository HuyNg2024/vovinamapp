<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_atg_phongban')) {
            Schema::create('table_atg_phongban', function (Blueprint $table) {
                $table->smallInteger('id');\n                $table->string('ten');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_atg_phongban');
    }
};
