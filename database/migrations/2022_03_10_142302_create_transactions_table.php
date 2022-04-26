<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('payment_date')->nullable();
            $table->string('voucher')->nullable();
            $table->string('voucher_type')->nullable();
            $table->string('voucher_state')->nullable();
            $table->string('voucher_link')->nullable();
            $table->foreignId('bank_id');
            $table->foreignId('user_id');
            $table->string('operation')->nullable();
            $table->boolean('state')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
