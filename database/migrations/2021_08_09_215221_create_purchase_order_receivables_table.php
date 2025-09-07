<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderReceivablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->double('qty_delivered')->unsigned()->default(0)->nullable();
            $table->double('qty_ordered')->unsigned()->default(0)->nullable();
            $table->string('order_no')->nullable();
            $table->string('delivery_no')->nullable();
            $table->string('cu_invoice_no')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->double('sub_total', 15, 2)->unsigned()->default(0)->nullable();
            $table->double('unit_price', 15, 2)->unsigned()->default(0)->nullable();
            $table->double('opening_stock', 15, 2)->default(0);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->date('received_date');
            $table->double('tax_rate', 15, 2)->default(0);
            $table->double('tax_amount', 15, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->double('qty_sold')->default(0);
            $table->string('batch_no')->nullable();
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
        Schema::dropIfExists('purchase_order_receivables');
    }
}
