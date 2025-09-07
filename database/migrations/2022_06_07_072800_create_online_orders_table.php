<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlineOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_id')->nullable();
            $table->integer('unit_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->double('qty', 15, 2)->default(0);
            $table->double('row_total', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);
            $table->double('selling_price', 15, 2)->default(0);
            $table->double('cost_total', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->integer('orderPrinted')->default(0);
            $table->date('order_date');
            $table->string('order_no')->nullable();
            $table->enum('isConfirmed', ['1', '0'])->default('0');
            $table->integer('accompaniment_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('email')->nullable();
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
        Schema::dropIfExists('online_orders');
    }
}
