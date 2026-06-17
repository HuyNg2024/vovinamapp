<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('table_class')) {
            Schema::create('table_class', function (Blueprint $table) {
                $table->id();
                $table->integer('id_city')->nullable();
                $table->integer('id_club')->nullable();
                $table->integer('id_atg_members')->nullable()->comment('HLV quan ly');
                $table->string('ten');
                $table->string('tenen')->nullable();
                $table->string('thoigian')->nullable();
                $table->string('thoigianen')->nullable();
                $table->integer('gia')->nullable();
                $table->string('diachi')->nullable();
                $table->string('dienthoai')->nullable();
                $table->string('ten_club')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_class');
    }
};
