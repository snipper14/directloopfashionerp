<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('ingredient_productions')){
        Schema::create('ingredient_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('material_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->double('material_qty')->default(0);
            $table->double('material_cost')->default(0);
            $table->double('total_materials_cost')->default(0);
          
            $table->double('desired_qty')->default(0);
            $table->double('cost_price')->default(0);
            $table->string('issue_no')->nullable();
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->date('production_date')->nullable();
            $table->string('production_no')->nullable();
            $table->enum("production_status",['progress','completed'])->default('progress');
            $table->boolean("end_product");
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
        Schema::dropIfExists('ingredient_productions');
    }
}
