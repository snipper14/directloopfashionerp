<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('table_bookings')) {
            Schema::create('table_bookings', function (Blueprint $table) {
                $table->id();
                $table->string("name")->nullable();
                $table->string("email")->nullable();
                $table->string("phone")->nullable();
                $table->dateTime("event_date");
                $table->integer("no_people");
                $table->string("details")->nullable();
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
        Schema::dropIfExists('table_bookings');
    }
}
