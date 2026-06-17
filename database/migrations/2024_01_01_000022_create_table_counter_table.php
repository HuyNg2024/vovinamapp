<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_counter')) {
            Schema::create('table_counter', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('tm')->nullable()->default(0);\n                $table->string('ip')->nullable()->default('0.0.0.0');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_counter');
    }
};
