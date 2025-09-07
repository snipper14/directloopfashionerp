<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('room_sales')){
        Schema::create('room_sales', function (Blueprint $table) {
            $table->id();
            $table->date('sold_on');
            $table->integer('room_reservation_id')->unsigned()->nullable();
            $table->double('amount_paid', 15, 2)->default(0);
            $table->double('cash_paid', 15, 2)->default(0);
            $table->double('mobile_money_paid', 15, 2)->default(0);
            $table->double('online_paid', 15, 2)->default(0);
            $table->double('card_paid', 15, 2)->default(0);
            $table->double('cheque_paid', 15, 2)->default(0);
            $table->double('bank_transfer_paid', 15, 2)->default(0);
            $table->integer('user_id')->unsigned();
            $table->integer('branch_id')->unsigned();
            $table->string('details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_sales');
    }
}
