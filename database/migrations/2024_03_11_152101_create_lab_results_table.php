<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('NO ACTION');
            $table->string("entry_code")->nullable();
            $table->string("patient_name")->nullable();
            $table->text("blood_test")->nullable();
            $table->string("imaging_results")->nullable();
            $table->text("other_test_results")->nullable();
            $table->string("lab_tech")->nullable();
            $table->string("blood_pressure")->nullable();
            $table->string("heart_rate")->nullable();
            $table->string("respiratory_rate")->nullable();
            $table->string("temperature")->nullable();
            $table->string("height_and_weight")->nullable();
            $table->enum("status",['pending','completed'])->default('pending');
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
        Schema::dropIfExists('lab_results');
    }
}
