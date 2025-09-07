<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovtStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('stock_movt_statuses')){
        Schema::create('stock_movt_statuses', function (Blueprint $table) {
            $table->id();
            $table->enum('updated',['0','1'])->default('0');
            $table->enum('location',['department','store']);
            $table->integer('user_id')->unsigned();
            $table->integer('branch_id')->unsigned();
            $table->date('date_recorded');
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
        Schema::dropIfExists('stock_movt_statuses');
    }
}
