<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('referee')) {
            Schema::create('referee', function (Blueprint $table) {
                $table->integer('id');\n                $table->string('name');\n                $table->string('username');\n                $table->string('password');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referee');
    }
};
