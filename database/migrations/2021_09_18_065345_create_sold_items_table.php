<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoldItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sold_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('location_id')->constrained('locations')->onDelete('NO ACTION');
            $table->foreignId('table_id')->constrained('tables')->onDelete('NO ACTION');
            $table->integer('department_id')->nullable();
            $table->date('order_date');
            $table->double('qty', 8, 1);
            $table->double('price', 15, 2)->default(0);
            $table->double('cost_price', 15, 2)->default(0);
            $table->double('cost_total', 15, 2)->default(0);
            $table->double('row_total', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);

            $table->string('order_type');

            $table->string('prev_order_no')->nullable();
            $table->string('merged_order_no')->nullable();
            $table->integer('accompaniment_id')->unsigned()->nullable();
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
        Schema::dropIfExists('sold_items');
    }
}
