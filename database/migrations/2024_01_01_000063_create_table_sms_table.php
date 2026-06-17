<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_sms')) {
            Schema::create('table_sms', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->integer('date');\n                $table->string('title')->nullable();\n                $table->text('description');\n                $table->integer('created_date');\n                $table->string('madonhang')->nullable();\n                $table->string('dienthoai')->nullable();\n                $table->double('sotien')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_sms');
    }
};
