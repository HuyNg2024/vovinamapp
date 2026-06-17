<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_ncs_task_status')) {
            Schema::create('table_ncs_task_status', function (Blueprint $table) {
                $table->tinyInteger('id');\n                $table->string('class');\n                $table->string('ten');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_ncs_task_status');
    }
};
