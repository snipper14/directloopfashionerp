<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomServiceSoldItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_service_sold_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->integer('department_id')->nullable()->unsigned();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('NO ACTION');
            $table->integer('room_reservation_id')->constrained('room_reservations')->onDelete('NO ACTION');
            $table->integer('room_service_sale_id')->default(0);
            $table->integer('room_package_id')->nullable()->unsigned();
            $table->date('order_date');
            $table->double('cost_price', 15, 2)->default(0);
            $table->double('cost_total', 15, 2)->default(0);
            $table->double('qty', 8, 1);
            $table->double('price', 15, 2)->default(0);
            $table->double('row_total', 15, 2)->default(0);
            $table->double('vat_rate', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);
            $table->double('no_guest', 15, 2)->default(1);
            $table->double('service_charge_amount', 15, 2)->default(0);
            $table->double('service_charge_rate', 15, 2)->default(0);
            $table->string('order_type')->nullable();

            $table->string('prev_order_no')->nullable();
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
        Schema::dropIfExists('room_service_sold_items');
    }
}
