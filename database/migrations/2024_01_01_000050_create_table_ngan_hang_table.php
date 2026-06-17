<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_ngan_hang')) {
            Schema::create('table_ngan_hang', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('ten');\n                $table->string('viettat');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_ngan_hang');
    }
};
