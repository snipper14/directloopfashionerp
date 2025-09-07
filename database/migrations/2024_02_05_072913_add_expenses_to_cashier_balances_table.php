<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpensesToCashierBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashier_balances', function (Blueprint $table) {
            $table->double('invoice_payments', 15, 2)->default(0);
            $table->double('cash_sales_amount', 15, 2)->default(0);
            $table->double('expense_payments', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashier_balances', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_payments','cash_sales_amount','expense_payments'
            ]);
        });
    }
}
