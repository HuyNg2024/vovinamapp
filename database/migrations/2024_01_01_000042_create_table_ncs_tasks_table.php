<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_ncs_tasks')) {
            Schema::create('table_ncs_tasks', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->string('ten');\n                $table->tinyInteger('status');\n                $table->date('date');\n                $table->tinyInteger('priority');\n                $table->bigInteger('member_id')->nullable();\n                $table->integer('created_date')->nullable();\n                $table->integer('updated_date')->nullable();\n                $table->integer('stt');\n                $table->tinyInteger('hienthi');\n                $table->string('noidung')->nullable();\n                $table->string('ghichu')->nullable();\n                $table->string('taskcode')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_ncs_tasks');
    }
};
