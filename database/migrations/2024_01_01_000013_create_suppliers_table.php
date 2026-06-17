<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->integer('SupplierID');\n                $table->string('SupplierName');\n                $table->string('Address');\n                $table->string('Phone');\n                $table->string('Email');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
