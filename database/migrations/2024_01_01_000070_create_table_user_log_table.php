<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_user_log')) {
            Schema::create('table_user_log', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('id_user')->nullable()->default(0);\n                $table->string('ip')->nullable()->default('0.0.0.0');\n                $table->integer('timelog')->nullable()->default(0);\n                $table->text('user_agent')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_user_log');
    }
};
