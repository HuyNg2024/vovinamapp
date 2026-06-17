<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('id_nhomquyen')->nullable()->default(0);\n                $table->string('username')->nullable();\n                $table->string('password')->nullable();\n                $table->string('maxacnhan')->nullable();\n                $table->string('avatar')->nullable();\n                $table->string('ten')->nullable();\n                $table->string('dienthoai')->nullable();\n                $table->string('email')->nullable();\n                $table->string('diachi')->nullable();\n                $table->boolean('gioitinh')->nullable()->default(0);\n                $table->string('login_session')->nullable();\n                $table->string('user_token')->nullable();\n                $table->string('lastlogin')->nullable();\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->string('quyen')->nullable();\n                $table->integer('role')->nullable()->default(1);\n                $table->integer('ngaysinh')->nullable()->default(0);\n                $table->integer('stt')->nullable()->default(0);\n                $table->integer('id_club')->nullable();\n                $table->string('bank_qrcode')->nullable();\n                $table->date('registerd_date')->nullable();\n                $table->string('serial_number')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
