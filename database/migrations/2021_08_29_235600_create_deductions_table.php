<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->string("deduction_type");
            $table->foreignId('employee_id')->constrained('employees')->onDelete('NO ACTION');
            $table->double("monthy_deduction", 15, 8);
            $table->double('outstanding_balance', 15, 8);
            $table->double("deduction_total", 15, 8);
            $table->date('effective_date');
            $table->date('end_date');
            $table->integer('no_of_months');
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
        Schema::dropIfExists('deductions');
    }
}
