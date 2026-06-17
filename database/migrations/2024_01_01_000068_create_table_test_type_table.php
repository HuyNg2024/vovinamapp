<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_test_type')) {
            Schema::create('table_test_type', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('ten');\n                $table->string('options');\n                $table->integer('stt');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_test_type');
    }
};
