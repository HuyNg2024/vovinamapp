<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_ketquathi')) {
            Schema::create('table_ketquathi', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->integer('id_exam');\n                $table->integer('id_member');\n                $table->integer('id_capdaiduthi');\n                $table->tinyInteger('ketqua');\n                $table->tinyInteger('tinhtrang');\n                $table->double('donluyen');\n                $table->double('canban');\n                $table->double('songluyen');\n                $table->double('doikhang');\n                $table->double('lythuyet');\n                $table->double('theluc');\n                $table->string('ghichu');\n                $table->integer('id_giamkhao');\n                $table->date('ngaycham');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_ketquathi');
    }
};
