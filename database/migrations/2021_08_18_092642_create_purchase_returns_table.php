<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('NO ACTION');

            $table->boolean('deduct_stock');
            $table->double("qty", 15, 2)->default('0');
            $table->double("unit_price", 15, 2)->default('0');
            $table->double("opening_stock", 15, 2)->default('0');
            $table->string('return_code');
            $table->string('order_no');
            $table->string("details");
            $table->date('return_date');
            $table->enum('status', ['complete', 'progress'])->default('progress');
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
        Schema::dropIfExists('purchase_returns');
    }
}
