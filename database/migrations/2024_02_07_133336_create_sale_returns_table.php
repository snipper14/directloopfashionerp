<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->string("trans_code")->nullable();
            $table->foreignId('replaced_stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->double('qty_replaced')->default(0);
            $table->double('replaced_purchase_price')->default(0);
            $table->double('replaced_selling_price')->default(0);
            $table->double('replaced_total')->default(0);
            $table->double('replaced_vat_total')->default(0);
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
        Schema::dropIfExists('sale_returns');
    }
}
