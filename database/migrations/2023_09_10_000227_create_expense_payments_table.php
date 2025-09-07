<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('account_id')->constrained('ledger_accounts')->onDelete('NO ACTION');
            $table->foreignId('expense_id')->constrained('expenses')->onDelete('NO ACTION');
            $table->double('expense_amount',15,2)->default(0);
            $table->double('amount_paid',15,2);
            $table->string("ref_no")->nullable();
            $table->date('date_paid');
            $table->string('payment_details')->nullable();
            $table->string('pay_method')->nullable();
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
        Schema::dropIfExists('expense_payments');
    }
}
