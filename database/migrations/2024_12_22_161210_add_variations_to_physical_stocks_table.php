<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariationsToPhysicalStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('physical_stocks', function (Blueprint $table) {
            $table->double('original_qty')->default(0);
            $table->double('selling_price')->default(0);
            $table->double('purchase_price')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('physical_stocks', function (Blueprint $table) {
            $table->dropColumn(['original_qty','selling_price','purchase_price']);
        });
    }
}
