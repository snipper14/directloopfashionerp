<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('NO ACTION');
            $table->integer("room_standard_id")->nullable()->unsigned();
            $table->foreignId('room_package_id')->constrained('room_packages')->onDelete('NO ACTION');
            $table->double('rate', 15, 2)->default(0);
            $table->double('meal_rate', 15, 2)->default(0);
            $table->double('room_rate', 15, 2)->default(0);
            
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
        Schema::dropIfExists('room_rates');
    }
}
