<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_member_info')) {
            Schema::create('table_member_info', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->integer('id_member');\n                $table->integer('heartrate');\n                $table->double('height');\n                $table->double('weight');\n                $table->double('fat');\n                $table->double('vf');\n                $table->string('rm');\n                $table->double('BMI');\n                $table->double('bodyage');\n                $table->double('subcutaneous_trunk');\n                $table->double('skeletalwholebody');\n                $table->double('muscle');\n                $table->double('bodyfat');\n                $table->double('trunkfat');\n                $table->double('excessedbodyfat');\n                $table->integer('date');\n                $table->double('options');\n                $table->integer('punches');\n                $table->integer('kicks');\n                $table->integer('pushup');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_member_info');
    }
};
