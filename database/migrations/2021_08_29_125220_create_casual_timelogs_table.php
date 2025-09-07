<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasualTimelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casual_timelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('NO ACTION');
            $table->double('no_hrs',10,2);
            $table->double('hrly_rate',15,2);
            $table->double('amount',15,2);
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->date('date_recorded');
            $table->boolean('isPaid')->default(0);
            $table->string('payroll_code')->nullable();
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
        Schema::dropIfExists('casual_timelogs');
    }
}
