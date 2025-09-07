<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('reservation_credit_notes')){
        Schema::create('reservation_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->string('credit_no')->nullable();
            $table->string('details')->nullable();
            $table->integer('room_reservation_id')->nullable()->unsigned();
            $table->double("qty")->default(0);
            $table->double('price')->default(0);
            $table->double("total")->default(0);
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
        Schema::dropIfExists('reservation_credit_notes');
    }
}
