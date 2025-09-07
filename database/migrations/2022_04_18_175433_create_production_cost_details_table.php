<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionCostDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('production_cost_details')) {
            Schema::create('production_cost_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
                $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
                $table->double('total_cost')->default(0);
                $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
                $table->string('production_no')->nullable();
                $table->string('issue_no')->nullable();
                $table->enum("production_status",['progress','completed'])->default('progress');
                $table->string('details')->nullable();
                $table->timestamps();
            });
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_cost_details');
    }
}
