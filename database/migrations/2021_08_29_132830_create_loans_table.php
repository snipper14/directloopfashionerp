<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('NO ACTION');
            $table->date('loan_date')->nullable();
            $table->double('loan_amount',15, 8)->nullable();
            $table->double('installment_amount', 15, 8)->nullable();
            $table->double('outstanding_balance',15, 8)->nullable();
            $table->date('completion_date')->nullable();
            $table->text('description')->nullable();
            $table->double('percent_interest')->nullable();
            $table->double('loan_period',8,2)->nullable();
            $table->string('loan_type');
            $table->double('total_payable_amount',8,2)->nullable();
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
        Schema::dropIfExists('loans');
    }
}
