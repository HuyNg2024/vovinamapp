<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_user_online')) {
            Schema::create('table_user_online', function (Blueprint $table) {
                $table->string('session')->nullable();\n                $table->integer('time')->nullable()->default(0);\n                $table->string('ip')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_user_online');
    }
};
