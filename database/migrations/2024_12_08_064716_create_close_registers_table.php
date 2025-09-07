<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCloseRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('close_registers', function (Blueprint $table) {
            $table->id();
            $table->double('opening_mpesa_amount')->default(0);
            $table->double('opening_cash_amount')->default(0);
            $table->double('cash_payments')->default(0);
            $table->double('mpesa_payments')->default(0);
            $table->double('card_payments')->default(0);
            $table->double('loyalty_payments')->default(0);
            $table->double('credit_payments')->default(0);
            $table->double('return_cash_sales')->default(0);
            $table->double('return_mpesa_sales')->default(0);
            $table->double('cash_drops')->default(0);
            $table->double('mpesa_drops')->default(0);
            $table->double('cash_supplier_payments')->default(0);
            $table->double('mpesa_supplier_payments')->default(0);
            $table->double('cash_customer_payments')->default(0);
            $table->double('mpesa_customer_payments')->default(0);
            $table->double('cash_expenses_payments')->default(0);
            $table->double('mpesa_expenses_payments')->default(0);
            $table->double('expected_cash')->default(0);
            $table->double('expected_mpesa')->default(0);
            $table->double('expected_card')->default(0);
            $table->double('closing_cash')->default(0);
            $table->double('closing_mpesa')->default(0);
            $table->double('closing_card')->default(0);
            $table->double('cash_shortover')->default(0);
            $table->double('mpesa_shortover')->default(0);
            $table->double('card_shortover')->default(0);
            $table->double('tax_collected')->default(0);
            $table->double('zero_rated')->default(0);
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->dateTime('opened_time')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('close_registers');
    }
}
