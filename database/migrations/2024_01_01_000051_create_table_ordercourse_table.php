<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_ordercourse')) {
            Schema::create('table_ordercourse', function (Blueprint $table) {
                $table->integer('id');\n                $table->integer('id_user')->nullable()->default(0);\n                $table->integer('id_member');\n                $table->string('madonhang')->nullable();\n                $table->string('lichhoc');\n                $table->integer('id_event');\n                $table->string('hoten')->nullable();\n                $table->string('dienthoai')->nullable();\n                $table->string('diachi')->nullable();\n                $table->string('email')->nullable();\n                $table->integer('httt')->nullable()->default(0);\n                $table->double('tamtinh')->nullable()->default(0);\n                $table->double('tonggia')->nullable()->default(0);\n                $table->integer('id_city')->nullable()->default(0);\n                $table->integer('id_district')->nullable()->default(0);\n                $table->integer('id_wards')->nullable()->default(0);\n                $table->double('phiship')->nullable()->default(0);\n                $table->double('phicoupon')->nullable()->default(0);\n                $table->integer('loaicoupon')->nullable()->default(0);\n                $table->integer('idcoupon')->nullable()->default(0);\n                $table->text('yeucaukhac')->nullable();\n                $table->text('ghichu')->nullable();\n                $table->text('noidung');\n                $table->integer('ngaytao')->nullable()->default(0);\n                $table->integer('ngaycapnhat');\n                $table->integer('ngayhuy')->nullable();\n                $table->integer('ngayhoanthanh')->nullable();\n                $table->integer('ngayxacnhan')->nullable();\n                $table->integer('ngaygiao')->nullable();\n                $table->integer('tinhtrang')->nullable()->default(0);\n                $table->integer('stt')->nullable()->default(0);\n                $table->tinyInteger('loai');\n                $table->integer('id_club');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_ordercourse');
    }
};
