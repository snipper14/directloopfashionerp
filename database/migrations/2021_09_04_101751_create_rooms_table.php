<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('room_standard_id')->constrained('room_standards')->onDelete('NO ACTION');
            $table->string('door_name');
            $table->string('qty_bed');
            $table->string('floor_no')->nullable();
            $table->string('details')->nullable();
            $table->enum('occupation',['accupied','empty'])->default('empty');
            $table->enum('usage_status',['ready','not_ready'])->default('ready');
            $table->enum('is_under_maintenance',['1','0'])->default('0');
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
        Schema::dropIfExists('rooms');
    }
}
