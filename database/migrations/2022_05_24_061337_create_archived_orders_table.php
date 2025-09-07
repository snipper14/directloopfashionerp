<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archived_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->integer('cashier_id')->constrained('users')->onDelete('NO ACTION');
            $table->integer('location_id')->nullable();
            $table->integer('table_id')->unsigned()->nullable();
            $table->integer('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->integer('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->integer('department_id')->constrained('departments')->onDelete('NO ACTION');

            $table->integer('customer_id')->nullable();
            $table->double('qty', 15, 2)->default(0);
            $table->double('price', 15, 2)->default(0);
            $table->double('row_total', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);
            $table->double('cost_price', 15, 2)->default(0);
            $table->double('cost_total', 15, 2)->default(0);
            $table->double('no_guest', 15, 2)->default(1);
            $table->string('notes')->nullable();
            $table->integer('orderPrinted')->default(0);
            $table->date('order_date');
            $table->dateTime('archived_at')->nullable();
            $table->string('order_no');
            $table->string('prev_order_no')->nullable();
            $table->string('merged_order_no')->nullable();

            $table->enum('isComplete', ['1', '0'])->default('0');
            $table->string('order_type');
            $table->integer('accompaniment_id')->nullable();
            $table->enum('isBillPrinted',['1','0'])->default('0');
            $table->string('customer_name')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('delivery_time')->nullable();
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
        Schema::dropIfExists('archived_orders');
    }
}
