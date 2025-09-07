<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialStoreIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('initial_store_issues')) {
        Schema::create('initial_store_issues', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->enum('status', ['complete', 'progress'])->default('progress');
            $table->double('qty_issued')->default(0);
            $table->double('opening_stock')->default(0);
            $table->double('qty_received')->default(0);
            $table->double('row_total')->default(0);
            $table->string('unit')->nullable();
            $table->date('date_issued');
            $table->date('date_received')->nullable();
            $table->double('purchase_price')->default(0);
            $table->enum('isReceived',['0','1'])->default('0');
            $table->enum('isSync',['0','1'])->default('0');
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
        Schema::dropIfExists('initial_store_issues');
    }
}
