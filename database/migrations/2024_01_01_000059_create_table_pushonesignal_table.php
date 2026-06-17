<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_pushonesignal')) {
            Schema::create('table_pushonesignal', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('number')->nullable()->default(0);\n                $table->string('name')->nullable();\n                $table->string('link')->nullable();\n                $table->string('photo')->nullable();\n                $table->text('description')->nullable();\n                $table->integer('status')->nullable()->default(0);\n                $table->string('date')->nullable();\n                $table->integer('times')->nullable()->default(0);\n                $table->integer('time_star')->nullable()->default(0);\n                $table->integer('gio')->nullable()->default(0);\n                $table->integer('phut')->nullable()->default(0);\n                $table->integer('solancon')->nullable()->default(0);\n                $table->integer('timegannhat')->nullable()->default(0);\n                $table->integer('test')->nullable()->default(0);\n                $table->integer('stt')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_pushonesignal');
    }
};
