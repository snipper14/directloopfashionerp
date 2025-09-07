<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientMedicalRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_medical_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('NO ACTION');
            $table->string("entry_code")->nullable();
            $table->text('pre_existing_medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('past_surgeries_medical_procedures')->nullable();
            $table->text('family_medical_history')->nullable();
            $table->text('current_medication')->nullable();
            $table->text('blood_test')->nullable();
            $table->text('imaging_results')->nullable();
            $table->text('other_test_results')->nullable();
            $table->dateTime('date_time_appointments')->nullable();
            $table->string('visiting_health_provider')->nullable();
            $table->text('purpose_of_visit')->nullable();
            $table->text('treatment_plans')->nullable();
            $table->text('healthcare_provider_report')->nullable();
            $table->text('symptoms_complaints')->nullable();
            $table->string('blood_pressure')->nullable();
            $table->string('heart_rate')->nullable();
            $table->string('respiratory_rate')->nullable();
            $table->string('temperature')->nullable();
            $table->string('height_and_weight')->nullable();

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
        Schema::dropIfExists('patient_medical_records');
    }
}
