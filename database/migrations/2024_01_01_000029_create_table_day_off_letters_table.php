<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_day_off_letters')) {
            Schema::create('table_day_off_letters', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->bigInteger('member_id');\n                $table->tinyInteger('loaiphep');\n                $table->date('ngaybatdau');\n                $table->date('ngayketthuc');\n                $table->tinyInteger('trangthai')->default(1);\n                $table->bigInteger('nguoi_duyet')->nullable();\n                $table->text('note')->nullable();\n                $table->integer('updated_date')->nullable();\n                $table->bigInteger('updated_userid')->nullable();\n                $table->integer('created_userid')->nullable();\n                $table->integer('created_date')->nullable();\n                $table->integer('updated_note')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_day_off_letters');
    }
};
