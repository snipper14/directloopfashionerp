<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockDisassembliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_disassemblies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('child_stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->double('child_qty')->default(0);
            $table->double('child_cost')->default(0);
            $table->double('child_totalcost')->default(0);
          
            $table->double('parent_qty')->default(0);
            $table->double('parent_cost_price')->default(0);
            $table->double('parent_totalcost')->default(0);
            $table->string('issue_no')->nullable();
            $table->foreignId('parent_department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('child_department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->date('disassembly_date')->nullable();
            $table->string('production_no')->nullable();
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
        Schema::dropIfExists('stock_disassemblies');
    }
}
