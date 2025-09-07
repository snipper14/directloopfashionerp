<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashierBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashier_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->double('opening_amount', 15, 2)->default(0);
            $table->double('closing_amount', 15, 2)->default(0);
            $table->double('sold_amount', 15, 2)->default(0);
            $table->double('cash_collected', 15, 2)->default(0);
            $table->double('mobile_money_collected', 15, 2)->default(0);
            $table->double('card_collected', 15, 2)->default(0);

            $table->double('cash_sold_amount', 15, 2)->default(0);
            $table->double('mobile_money_sold_amount', 15, 2)->default(0);
            $table->double('card_sold_amount', 15, 2)->default(0);
            $table->double('conference_sales', 15, 2)->default(0);
            
            $table->dateTime("opening_time")->nullable();
            $table->dateTime("closing_time")->nullable();
            $table->enum('pending_closing', ['0', '1'])->default('1');
            $table->string('details')->nullable();
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
        Schema::dropIfExists('cashier_balances');
    }
}
