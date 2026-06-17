<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_member')) {
            Schema::create('table_member', function (Blueprint $table) {
                $table->integer('id');\n                $table->boolean('id_social')->nullable()->default(0);\n                $table->string('username')->nullable();\n                $table->string('password')->nullable();\n                $table->string('maxacnhan')->nullable();\n                $table->string('avatar')->nullable();\n                $table->string('ten')->nullable();\n                $table->string('dienthoai')->nullable();\n                $table->string('email')->nullable();\n                $table->string('diachi')->nullable();\n                $table->boolean('gioitinh')->nullable()->default(0);\n                $table->string('login_session')->nullable();\n                $table->string('lastlogin')->nullable();\n                $table->boolean('hienthi')->nullable()->default(0);\n                $table->integer('ngaysinh')->nullable()->default(0);\n                $table->integer('stt')->nullable()->default(0);\n                $table->tinyInteger('loai');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_member');
    }
};
