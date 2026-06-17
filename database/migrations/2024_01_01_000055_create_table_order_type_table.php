<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_order_type')) {
            Schema::create('table_order_type', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('ten')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_order_type');
    }
};
