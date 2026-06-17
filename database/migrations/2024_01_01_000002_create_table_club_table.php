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
        if (!Schema::hasTable('table_club')) {
            Schema::create('table_club', function (Blueprint $table) {
                $table->id();
                $table->integer('id_city')->nullable();
                $table->integer('id_district')->nullable();
                $table->string('ten');
                $table->string('tenen')->nullable();
                $table->string('tenkhongdau')->nullable();
                $table->string('diachi')->nullable();
                $table->string('dienthoai')->nullable();
                $table->string('email')->nullable();
                $table->string('thoigianhoc')->nullable();
                $table->string('map_lat')->nullable();
                $table->string('map_long')->nullable();
                $table->integer('id_class')->nullable();
                $table->string('img')->nullable();
                $table->string('bank_qrcode')->nullable();
                $table->string('image')->nullable();
                $table->integer('id_atg_members')->nullable()->comment('HLV cua CLB');
                $table->integer('sn')->nullable();
                $table->boolean('hienthi')->default(1);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_club');
    }
};
