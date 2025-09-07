<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('approver_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->string('vendor_reference')->nullable();
            $table->string('order_no')->nullable();
            $table->string('shipping_address')->nullable();
            $table->date('order_deadline');
            $table->date('receipt_date');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->double('qty');
            $table->double('qty_delivered')->unsigned()->default(0)->nullable();
            $table->double('unit_price',15,2);
            $table->double('taxes',15,2);
            $table->double('tax_rate',15,2);
            $table->double('sub_total',15,2);
            $table->enum('progress',['progress','completed'])->default('progress');
            $table->enum('internal_confirmation',['confirmed','pending'])->default('pending');
            $table->enum('supplier_confirmation',['confirmed','pending'])->default('pending');
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
        Schema::dropIfExists('purchase_orders');
    }
}
