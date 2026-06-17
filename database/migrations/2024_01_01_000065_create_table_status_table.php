<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_status')) {
            Schema::create('table_status', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('trangthai')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_status');
    }
};
