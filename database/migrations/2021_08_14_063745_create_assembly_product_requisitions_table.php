<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssemblyProductRequisitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assembly_product_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('ingredient_id')->constrained('stocks')->onDelete('NO ACTION');
           
          
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            
            $table->double('qty',15,2)->default(0)->nullable();
            $table->double('ingredient_price',15,2)->default(0)->nullable();
            $table->double('ingredient_qty',15,2)->default(0)->nullable();
            $table->double('ingredient_qty_issued',15,2)->default(0)->nullable();
            $table->double('final_qty_produced',15,2)->default(0)->nullable();
            $table->double('sub_total',15,2)->default(0)->nullable();
            
            $table->dateTime('date_due');
            $table->integer('approved')->default('0');
            $table->enum('status',['progress','complete'])->default('progress');
            $table->integer('is_stock_updated')->default(0);
            $table->string('req_id')->nullable();
            $table->string('details')->nullable();
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
        Schema::dropIfExists('assembly_product_requisitions');
    }
}
