<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHouseKeepingConsumablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('house_keeping_consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('NO ACTION');
            $table->foreignId('room_package_id')->constrained('room_packages')->onDelete('NO ACTION');
            $table->foreignId('house_keeper_id')->constrained('employees')->onDelete('NO ACTION'); 
            $table->integer('room_reservation_id')->nullable()->unsigned();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->double('qty')->default(0);
            $table->double('price')->default(0);
            $table->double('total')->default(0);
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
        Schema::dropIfExists('house_keeping_consumables');
    }
}
