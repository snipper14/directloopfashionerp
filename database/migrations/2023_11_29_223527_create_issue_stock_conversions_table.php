<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssueStockConversionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issue_stock_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no');
            $table->foreignId('parent_stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('department_to_id')->constrained('departments')->onDelete('NO ACTION');

            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('issued_staff_id')->constrained('users')->onDelete('NO ACTION');

            $table->enum('status', ['complete', 'progress'])->default('progress');
            $table->double('qty_issued')->default(0);


            $table->double('parent_issue_qty')->default(0);

            $table->date('date_issued');
            $table->double('mapping_value')->default(0);

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
        Schema::dropIfExists('issue_stock_conversions');
    }
}
