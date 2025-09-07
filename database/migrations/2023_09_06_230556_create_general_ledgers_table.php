<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->integer('account_id')->nullable();
           
            $table->string("other_account_name")->nullable();
          $table->string("ref")->nullable();
            $table->string("account_type")->nullable();
           
            $table->string("entry_code")->nullable();
            $table->string("details")->nullable();
            $table->double('debit_amount')->default(0);
            $table->double('credit_amount')->default(0);
            $table->enum("status", ["completed", "pending"])->default("completed");
            $table->date("entry_date");
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
        Schema::dropIfExists('general_ledgers');
    }
}
