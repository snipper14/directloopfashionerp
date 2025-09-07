<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePOSOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_o_s_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->integer('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->double('qty', 15, 2)->default(0);
            $table->double('selling_price', 15, 2)->default(0);
            $table->double('row_amount', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);
            $table->date('order_date');
            $table->string('order_no');
            $table->double('tax_rate', 15, 2)->default(0);
           
            $table->string('product_name');
            $table->string('code');
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
        Schema::dropIfExists('p_o_s_orders');
    }
}
