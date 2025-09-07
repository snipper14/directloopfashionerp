<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoidedSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voided_sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->integer('guest_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->integer('location_id')->nullable();
            $table->integer('table_id')->nullable();
            $table->integer('room_id')->nullable();
            $table->integer('room_reservation_id')->nullable();
            $table->date('order_date');
            $table->double('cost_price', 15, 2)->default(0);
            $table->double('cost_total', 15, 2)->default(0);
            $table->double('qty',8,1);
            $table->double('price', 15, 2)->default(0);
            $table->double('row_total', 15, 2)->default(0);
            $table->double('vat_rate', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);
            $table->double('no_guest', 15, 2)->default(1);
             $table->string('order_type')->nullable();
             $table->string('notes')->nullable();
             $table->integer('accompaniment_id')->unsigned()->nullable();
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
        Schema::dropIfExists('voided_sales');
    }
}
