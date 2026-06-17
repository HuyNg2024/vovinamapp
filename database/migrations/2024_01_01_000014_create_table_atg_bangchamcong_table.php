<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_atg_bangchamcong')) {
            Schema::create('table_atg_bangchamcong', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->bigInteger('member_id');\n                $table->integer('nam');\n                $table->tinyInteger('thang');\n                $table->string('1')->nullable();\n                $table->string('2')->nullable();\n                $table->string('3')->nullable();\n                $table->string('4')->nullable();\n                $table->string('5')->nullable();\n                $table->string('6')->nullable();\n                $table->string('7')->nullable();\n                $table->string('8')->nullable();\n                $table->string('9')->nullable();\n                $table->string('10')->nullable();\n                $table->string('11')->nullable();\n                $table->string('12')->nullable();\n                $table->string('13')->nullable();\n                $table->string('14')->nullable();\n                $table->string('15')->nullable();\n                $table->string('16')->nullable();\n                $table->string('17')->nullable();\n                $table->string('18')->nullable();\n                $table->string('19')->nullable();\n                $table->string('20')->nullable();\n                $table->string('21')->nullable();\n                $table->string('22')->nullable();\n                $table->string('23')->nullable();\n                $table->string('24')->nullable();\n                $table->string('25')->nullable();\n                $table->string('26')->nullable();\n                $table->string('27')->nullable();\n                $table->string('28')->nullable();\n                $table->string('29')->nullable();\n                $table->string('30')->nullable();\n                $table->string('31')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_atg_bangchamcong');
    }
};
