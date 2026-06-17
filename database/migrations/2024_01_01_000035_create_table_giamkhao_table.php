<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_giamkhao')) {
            Schema::create('table_giamkhao', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('firstname');\n                $table->string('lastname');\n                $table->text('username');\n                $table->text('password');\n                $table->text('avatar')->nullable();\n                $table->date('last_login')->nullable();\n                $table->boolean('type')->default(0);\n                $table->date('date_added');\n                $table->date('date_updated')->nullable();\n                $table->string('code');\n                $table->string('personID');\n                $table->string('personImage')->default('HTTP://SANDICHVU.COM.VN/IMAGES/LOGOS/LOGO.PNG');\n                $table->tinyInteger('deleted');\n                $table->string('phone');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_giamkhao');
    }
};
