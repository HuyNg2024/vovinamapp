<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('register_class')) {
            Schema::create('register_class', function (Blueprint $table) {
                $table->integer('id_atg_members');\n                $table->integer('id_class');\n                $table->date('begin_date')->nullable();\n                $table->date('end_date')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('register_class');
    }
};
