<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('NO ACTION');
            $table->date('payroll_from')->nullable();
            $table->date('payroll_to')->nullable();
            $table->double('housing_levy', 15, 8)->nullable();
             $table->double('basic_salary', 15, 8)->nullable();
             $table->double('total_allowance', 15, 8)->nullable();
             $table->double('total_rewards', 15, 8)->nullable();
             $table->double('nssf', 15, 8)->nullable();
             $table->double('income_tax', 15, 8)->nullable();
             $table->double('tax_relief', 15, 8)->nullable();
             $table->double('net_paye', 15, 8)->nullable();
             $table->double('pay_after_tax', 15, 8)->nullable();
             $table->double('total_advance', 15, 8)->nullable();
             $table->double('total_loans', 15, 8)->nullable();
             $table->double('total_overtime', 15, 8)->nullable();
             $table->double('net_salary', 15, 8)->nullable();
             $table->double('no_hrs',5,2)->default(0);
             $table->string('payment_type')->nullable();
             $table->string('payroll_code')->nullable();
             $table->double('total_deduction',15,5);
             $table->double('nhif', 15, 8)->nullable();
             $table->double('insurance_relief', 15, 8)->nullable();
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
        Schema::dropIfExists('payrolls');
    }
}
