<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_checkin')) {
            Schema::create('table_checkin', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->bigInteger('ma_nv');\n                $table->date('date')->nullable();\n                $table->integer('created_date')->nullable();\n                $table->bigInteger('created_userid')->nullable();\n                $table->integer('updated_date')->nullable();\n                $table->string('in')->nullable();\n                $table->string('out')->nullable();\n                $table->text('note')->nullable();\n                $table->bigInteger('updated_userid')->nullable();\n                $table->text('updated_note');\n                $table->text('options');\n                $table->tinyInteger('loaiphep')->default(3);\n                $table->tinyInteger('loaiphep')->default(3);\n                $table->tinyInteger('loaiphep')->default(3);\n                $table->integer('id_class');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_checkin');
    }
};
