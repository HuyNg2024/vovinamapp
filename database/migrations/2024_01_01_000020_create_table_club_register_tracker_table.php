<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_club_register_tracker')) {
            Schema::create('table_club_register_tracker', function (Blueprint $table) {
                $table->bigInteger('id');\n                $table->string('owner');\n                $table->text('note')->nullable();\n                $table->string('name');\n                $table->text('desc')->nullable();\n                $table->integer('date')->nullable();\n                $table->integer('date_approved');\n                $table->string('map_lat')->nullable();\n                $table->string('map_long')->nullable();\n                $table->bigInteger('id_club')->nullable();\n                $table->string('type')->nullable();\n                $table->integer('id_country')->nullable();\n                $table->integer('id_city')->nullable();\n                $table->integer('id_district')->nullable();\n                $table->integer('id_ward')->nullable();\n                $table->string('phone');\n                $table->string('timeopen');\n                $table->string('address')->nullable();\n                $table->string('contact')->nullable();\n                $table->string('email')->nullable();\n                $table->bigInteger('id_user')->nullable();\n                $table->integer('date_update');\n                $table->string('update_note');\n                $table->text('photo');\n                $table->smallInteger('status')->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_club_register_tracker');
    }
};
