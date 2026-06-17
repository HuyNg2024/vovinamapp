<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order')) {
            Schema::create('order', function (Blueprint $table) {
                $table->integer('id_cart');\n                $table->bigInteger('member_id')->nullable();\n                $table->string('txn_ref');\n                $table->integer('amount');\n                $table->string('order_info');\n                $table->string('response_code');\n                $table->string('transaction_no');\n                $table->string('bank_code');\n                $table->timestamp('pay_date');\n                $table->string('status');\n                $table->timestamp('created_at')->nullable();\n                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
