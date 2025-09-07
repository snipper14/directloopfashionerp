<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_no');
            $table->double('qty', 15, 2);
            $table->date("delivery_date");
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('NO ACTION');
            $table->string('invoice_no');
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
        Schema::dropIfExists('deliveries');
    }
}
