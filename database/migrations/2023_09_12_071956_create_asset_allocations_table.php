<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('NO ACTION');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('NO ACTION');
            $table->string('allocation_code')->nullable();
            $table->string('reason')->nullable();
            $table->dateTime('allocated_from')->nullable();
            $table->dateTime('allocated_to')->nullable();
            $table->double('qty')->default(1);
            $table->enum("allocation_status",['allocated','revoked'])->default('allocated');
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
        Schema::dropIfExists('asset_allocations');
    }
}
