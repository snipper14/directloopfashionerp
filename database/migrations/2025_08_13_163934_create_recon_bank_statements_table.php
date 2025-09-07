<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReconBankStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recon_bank_statements', function (Blueprint $table) {
            $table->id();
               $table->integer('account_id')->nullable();
            $table->integer('general_ledger_id')->nullable();
            $table->date('transaction_date');
            $table->double('credit_amount')->default(0);
            $table->double('debit_amount')->default(0);
             $table->double('opening_balance')->default(0);
            $table->double('closing_balance')->default(0);
            $table->string('ref_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->string('details')->nullable();
            $table->enum("status",['pending','reconciled'])->default('pending');
            
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
        Schema::dropIfExists('recon_bank_statements');
    }
}
