<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('direct_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('approver_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');

            $table->string('order_no')->nullable();

            $table->date('order_deadline');
            $table->date('order_date');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->double('qty');
            $table->double('qty_delivered')->unsigned()->default(0)->nullable();
            $table->double('unit_price', 15, 2);
            $table->double('taxes', 15, 2)->default(0);
            $table->double('tax_rate', 15, 2)->default(0);
            $table->double('sub_total', 15, 2);
            $table->enum('progress', ['progress', 'completed'])->default('progress');
            $table->enum('internal_confirmation', ['confirmed', 'pending'])->default('pending');
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
        Schema::dropIfExists('direct_purchase_orders');
    }
}
