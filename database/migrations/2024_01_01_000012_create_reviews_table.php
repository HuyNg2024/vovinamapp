<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->integer('ReviewID');\n                $table->integer('ProductID');\n                $table->string('RatingValue');\n                $table->integer('RatingCount');\n                $table->string('UserName');\n                $table->date('ReviewDate');\n                $table->text('ReviewContent');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
