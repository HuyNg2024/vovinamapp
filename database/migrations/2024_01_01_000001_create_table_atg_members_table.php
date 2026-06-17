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
        if (!Schema::hasTable('table_atg_members')) {
            Schema::create('table_atg_members', function (Blueprint $table) {
                $table->id();
                $table->string('username')->unique();
                $table->string('password');
                $table->string('email')->unique();
                $table->string('ten');
                $table->string('tenen')->nullable();
                $table->string('dienthoai')->unique();
                $table->string('diachi')->nullable();
                $table->string('ngaysinh')->nullable(); 
                $table->integer('gioitinh')->default(1)->comment('1: Nam, 0: Nu');
                $table->string('hotengiamho')->nullable();
                $table->string('dienthoai_giamho')->nullable();
                $table->string('lastlogin')->nullable();
                $table->integer('id_club')->nullable();
                $table->integer('id_capdai')->nullable();
                $table->double('chieucao')->nullable();
                $table->double('cannang')->nullable();
                $table->boolean('deleted')->default(0);
                $table->string('active_token')->nullable();
                $table->string('thietbi')->nullable();
                $table->integer('hlv flag')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_atg_members');
    }
};
