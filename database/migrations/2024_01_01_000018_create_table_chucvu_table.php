<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_chucvu')) {
            Schema::create('table_chucvu', function (Blueprint $table) {
                $table->smallInteger('id');\n                $table->string('ten');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_chucvu');
    }
};
