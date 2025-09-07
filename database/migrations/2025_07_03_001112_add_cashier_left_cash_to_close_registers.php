<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashierLeftCashToCloseRegisters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('close_registers', function (Blueprint $table) {
            $table->double("cashier_left_cash")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('close_registers', function (Blueprint $table) {
            $table->dropColumn(['cashier_left_cash']);
        });
    }
}
