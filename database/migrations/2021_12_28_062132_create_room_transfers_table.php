<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('room_transfers')){
        Schema::create('room_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('NO ACTION');
            $table->integer('room_rate_id')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->string('name')->nullable();
            $table->string('id_no')->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->integer('no_guest');
            $table->dateTime('from');
            $table->dateTime('to');
            $table->string('details')->nullable();
            $table->enum('paid', ['1', '0'])->nullable();
            $table->string('pay_method')->nullable();
            $table->string('ref_no')->nullable();
            $table->double('price', 15, 2)->default(0);
            $table->double('total', 15, 2)->default(0);
            $table->double('qty', 15, 2)->default(0);
            $table->double('tax_rate', 15, 2)->default(0);
            $table->double('tax_amount', 15, 2)->default(0);
            $table->double('amount_paid', 15, 2)->default(0);

            $table->double('cash_paid', 15, 2)->default(0);
            $table->double('mobile_money_paid', 15, 2)->default(0);
            $table->double('card_paid', 15, 2)->default(0);
            $table->double('cheque_paid', 15, 2)->default(0);
            $table->double('bank_transfer_paid', 15, 2)->default(0);


            $table->dateTime('checkout_date')->nullable();
            $table->integer('house_keeper_id')->nullable()->unsigned();
            $table->integer('waiter_id')->nullable()->unsigned();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('NO ACTION');
            $table->foreignId('room_package_id')->constrained('room_packages')->onDelete('NO ACTION');
            $table->foreignId('room_reservation_id')->constrained('room_reservations')->onDelete('NO ACTION');

            
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
        Schema::dropIfExists('room_transfers');
    }
}
