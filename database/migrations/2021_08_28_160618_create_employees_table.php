<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('id_no')->nullable();
            $table->string('dob')->nullable();
            $table->string('phone')->nullable() ;
            $table->string('email')->nullable();
            $table->string('other')->nullable();
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('employee_type_id')->constrained('employee_types')->onDelete('NO ACTION');
            $table->double('salary', 15, 8)->default(0);
            $table->double('hrly_rate', 15, 8)->nullable();
            $table->string('payment_type')->nullable();
            $table->string('kra_pin')->nullable();
            $table->string('job_no')->nullable();
            $table->string('start_contract');
            $table->boolean('deduct_nssf');
            $table->boolean('deduct_nhif');
            $table->boolean('deduct_paye');
            $table->boolean('deduct_housinglevy');
            $table->string('pay_method')->default('bank_transfer');
            $table->string('bank_branch')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('payment_phone')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
