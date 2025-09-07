<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomServiceSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_service_sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->string('receipt_no');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->date('order_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->double('qty', 8, 1);

            $table->double('receipt_total', 15, 2)->default(0);
            $table->double('receipt_balance', 15, 2)->default(0);
            $table->double('total_vat', 15, 2)->default(0);
            $table->double('service_charge_total', 15, 2)->default(0);
            $table->double('cash_pay', 15, 2)->default(0);
            $table->double('mpesa_pay', 15, 2)->default(0);
            $table->double('card_pay', 15, 2)->default(0);
            $table->double('credit', 15, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->string('payment_ref')->nullable();
            $table->enum('cashier_status',['cleared','uncleared']);
            $table->integer('waiter_id')->unsigned()->nullable();
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
        Schema::dropIfExists('room_service_sales');
    }
}
