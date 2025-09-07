<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('returned_stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->double('qty_returned')->default(0);
            $table->double('returned_purchase_price')->default(0);
            $table->double('returned_selling_price')->default(0);
            $table->double('returned_total')->default(0);
            $table->double('returned_vat_total')->default(0);
           
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
        Schema::dropIfExists('sale_exchanges');
    }
}
