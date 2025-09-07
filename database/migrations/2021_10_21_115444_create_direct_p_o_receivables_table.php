<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectPOReceivablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('direct_p_o_receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direct_purchase_order_id')->constrained('direct_purchase_orders')->onDelete('NO ACTION');
            $table->foreignId('supplier_id')->nullable();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->double('qty_delivered')->unsigned()->default(0)->nullable();
            $table->double('qty_ordered')->unsigned()->default(0)->nullable();
            $table->string('order_no')->nullable();
            $table->string('delivery_no')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->double('sub_total', 15, 2)->unsigned()->default(0)->nullable();
            $table->double('unit_price', 15, 2)->unsigned()->default(0)->nullable();
            $table->double('opening_stock')->default(0);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->date('received_date');
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
        Schema::dropIfExists('direct_p_o_receivables');
    }
}
