<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_educationgrades')) {
            Schema::create('table_educationgrades', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('code');\n                $table->string('ten');\n                $table->integer('order');\n                $table->integer('type');\n                $table->integer('level');\n                $table->integer('column1')->nullable();\n                $table->integer('column2')->nullable();\n                $table->integer('column3');\n                $table->date('date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_educationgrades');
    }
};
