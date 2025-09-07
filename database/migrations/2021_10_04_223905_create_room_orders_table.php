<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->integer('guest_id')->constrained('guests')->onDelete('NO ACTION');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('NO ACTION');
            $table->integer('room_package_id')->nullable()->unsigned();
              $table->integer('department_id')->nullable()->unsigned();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->integer('orderPrinted')->default(0);
            $table->integer('room_reservation_id')->constrained('room_reservations')->onDelete('NO ACTION');
            $table->double('qty', 15, 2)->default(0);
            $table->double('cost_price', 15, 2)->default(0);
            $table->double('cost_total', 15, 2)->default(0);
            $table->double('price', 15, 2)->default(0);
            $table->double('sub_total', 15, 2)->default(0);
            $table->double('row_total', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);
            $table->double('vat_rate', 15, 2)->default(0);
            $table->double('no_guest', 15, 2)->default(1);
            $table->double('service_charge_amount', 15, 2)->default(0);
            $table->double('service_charge_rate', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->date('order_date');
            $table->string('order_no');
            $table->string('prev_order_no')->nullable();
            $table->string('merged_order_no')->nullable();
            $table->enum('isComplete', ['1', '0'])->default('0');
            $table->string('order_type');
            $table->integer( 'accompaniment_id')->unsigned()->default();
            $table->enum("isBillPrinted",['0','1'])->default('0');
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
        Schema::dropIfExists('room_orders');
    }
}
