<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQBBankingReconciliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q_b_banking_reconciliations', function (Blueprint $table) {
   $table->id();
           $table->integer('general_ledger_id')->nullable();
          $table->integer( 'account_id')->nullable();
            $table->date('entry_date');
            $table->enum('status', ['completed', 'pending', 'uncleared'])->default('pending');
            $table->decimal('dr_amount', 15, 2)->default(0.00);
            $table->decimal('cr_amount', 15, 2)->default(0.00);
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('closing_balance', 15, 2)->default(0.00);
            $table->string('ref')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q_b_banking_reconciliations');
    }
}
