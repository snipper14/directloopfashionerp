<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('NO ACTION');
            $table->string('ref');
            $table->string('type');
            $table->date('entry_date');
            $table->string('description');
            $table->double('debit',15,2)->default('0');
            $table->double('credit',15,2)->default('0');
            $table->double('amount_due',15,3)->default('0');
            $table->double('balance',15,3)->default('0');
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
        Schema::dropIfExists('credit_ledgers');
    }
}
