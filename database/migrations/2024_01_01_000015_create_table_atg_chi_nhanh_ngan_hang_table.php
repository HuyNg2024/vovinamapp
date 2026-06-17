<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_atg_chi_nhanh_ngan_hang')) {
            Schema::create('table_atg_chi_nhanh_ngan_hang', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('tenchinhanh');\n                $table->integer('machinhanh');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_atg_chi_nhanh_ngan_hang');
    }
};
