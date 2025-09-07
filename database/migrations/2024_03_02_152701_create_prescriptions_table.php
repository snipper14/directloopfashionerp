<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->integer('patient_medical_record_id')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('NO ACTION');
            $table->string("product_name")->nullable();
            $table->string("entry_code")->nullable();
            $table->string("repeat_pattern")->nullable();
            $table->string("note")->nullable();
            $table->double('qty')->default(0);
            $table->dateTime("prescription_date_time")->nullable();
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
        Schema::dropIfExists('prescriptions');
    }
}
