<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_donviquanly')) {
            Schema::create('table_donviquanly', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('ten')->nullable();\n                $table->string('tenkhongdau')->nullable();\n                $table->string('matp')->nullable();\n                $table->integer('stt')->nullable()->default(0);\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->integer('ngaytao')->nullable()->default(0);\n                $table->integer('ngaysua')->nullable()->default(0);\n                $table->double('gia')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_donviquanly');
    }
};
