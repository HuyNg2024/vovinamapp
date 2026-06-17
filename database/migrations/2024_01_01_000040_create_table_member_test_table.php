<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_member_test')) {
            Schema::create('table_member_test', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->integer('id_type');\n                $table->integer('value');\n                $table->integer('date');\n                $table->integer('id_member');\n                $table->integer('heartrate');\n                $table->integer('spo2');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_member_test');
    }
};
