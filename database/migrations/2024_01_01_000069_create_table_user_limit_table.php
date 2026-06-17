<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('table_user_limit')) {
            Schema::create('table_user_limit', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('login_ip');\n                $table->integer('login_attempts');\n                $table->integer('attempt_time');\n                $table->integer('locked_time');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_user_limit');
    }
};
