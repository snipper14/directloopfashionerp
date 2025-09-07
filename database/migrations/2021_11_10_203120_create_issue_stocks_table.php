<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssueStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('issue_stocks')) {
        Schema::create('issue_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->foreignId('source_department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('issued_staff_id')->constrained('users')->onDelete('NO ACTION');
            
            $table->enum('status', ['complete', 'progress'])->default('progress');
            $table->string('internal_issue_no')->nullable();
            $table->double('qty_issued');
            $table->double('opening_stock');
          

            $table->double('final_qty');
            $table->double('row_total');
            $table->string('unit');
            $table->date('date_issued');
            $table->double('mapping_value');
            $table->double('purchase_price');
            $table->softDeletes();
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
        Schema::dropIfExists('issue_stocks');
    }
}
