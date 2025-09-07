<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('acc_code')->nullable();
             $table->string('company_phone')->nullable();
             $table->string('address')->nullable();
             $table->string('postal_address')->nullable();
             $table->string('city')->nullable();
             $table->string('country')->nullable();
             $table->string('company_email')->nullable();
             $table->string('bank_name')->nullable();
             $table->string('bank_branch')->nullable();
             $table->string('account_name')->nullable();
             $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
             $table->string('account_no')->nullable();
             $table->string('contact_person')->nullable();
             $table->string('contact_email')->nullable();
             $table->string('contact_phone')->nullable();
             $table->string('pin')->nullable();
             $table->double('credit_limit')->default(0);
             $table->integer('price_group_id')->nullable();
             $table->enum("vat_deduction",['vatable','exempted'])->default('vatable');
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
        Schema::dropIfExists('customers');
    }
}
