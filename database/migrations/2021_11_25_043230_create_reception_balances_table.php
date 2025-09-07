<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceptionBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('reception_balances')) {
        Schema::create('reception_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->double('opening_amount', 15, 2)->default(0);
            $table->double('closing_amount', 15, 2)->default(0);
            $table->double('sold_amount', 15, 2)->default(0);
            $table->double('total_amount', 15, 2)->default(0);
            
            $table->double('cash_amount', 15, 2)->default(0);
            $table->double('bank_transfer_amount', 15, 2)->default(0);
            $table->double('deficit_amount', 15, 2)->default(0);
            
            $table->double('mobile_money', 15, 2)->default(0);
            $table->double('online_paid', 15, 2)->default(0);
            $table->double('credit_card', 15, 2)->default(0);
            $table->double('cheque', 15, 2)->default(0);

            $table->double('cash_sold_amount', 15, 2)->default(0);
            $table->double('mobile_money_sold_amount', 15, 2)->default(0);
            $table->double('online_sold_amount', 15, 2)->default(0);
            $table->double('card_sold_amount', 15, 2)->default(0);
            $table->double('bank_transfer_sold_amount', 15, 2)->default(0);
            $table->double('cheque_sold_amount', 15, 2)->default(0);
            
            $table->dateTime("opening_time");
            $table->dateTime("closing_time");
            $table->enum('pending_closing', ['0', '1'])->default('1');
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
        Schema::dropIfExists('reception_balances');
    }
}
