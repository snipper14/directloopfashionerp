<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReqDispatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('req_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('issuer_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->string("details")->nullable();
            $table->dateTime('issue_date');
            $table->double("dispatch_qty",15,2)->default(0)->nullable();
            $table->double("order_qty",15,2)->default(0)->nullable();
            $table->double("pending_qty",15,2)->default(0)->nullable();
            $table->string("req_id")->nullable();
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
        Schema::dropIfExists('req_dispatches');
    }
}
