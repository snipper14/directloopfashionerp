<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSickoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sickoffs', function (Blueprint $table) {
            $table->id();
             $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('NO ACTION');
            $table->string("age")->nullable();
            $table->string("entry_code")->nullable();
            $table->string("reg_no")->nullable();
            $table->enum("gender", ['male', 'female'])->nullable();
            $table->integer('sickoff_days')->nullable();
            $table->date('startdate');
            $table->date('enddate');
            $table->date('currentdate');
            $table->string('patient_name')->nullable();
            $table->string('doctors_name')->nullable();
            $table->text('doctors_report')->nullable();
            $table->double('cost')->default(0);
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
        Schema::dropIfExists('sickoffs');
    }
}
