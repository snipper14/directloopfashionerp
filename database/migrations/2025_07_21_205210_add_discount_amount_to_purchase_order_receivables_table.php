<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountAmountToPurchaseOrderReceivablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_receivables', function (Blueprint $table) {
            $table->double('discount_amount', 15, 2)->default(0)->after('tax_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_receivables', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
}
