<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_ordercourse_file')) {
            Schema::create('table_ordercourse_file', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('ten');\n                $table->string('ghichu');\n                $table->integer('id_order');\n                $table->string('taptin');\n                $table->integer('ngaytao');\n                $table->string('loai')->nullable();\n                $table->integer('id_user')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_ordercourse_file');
    }
};
