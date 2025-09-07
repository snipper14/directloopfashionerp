<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayMethodToRetailSalesRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('retail_sales_refunds', function (Blueprint $table) {
            $table->string("pay_method")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('retail_sales_refunds', function (Blueprint $table) {
            $table->dropColumn('pay_method');
        });
    }
}
