<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stock_movts')) {
            Schema::create('stock_movts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
                $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
                $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
                $table->enum('is_physical_update', ['1', '0'])->default('0');

                $table->double('opening_stock')->default(0);
                $table->double('opening_dept_stock')->default(0);
                $table->integer('is_internal_stock')->default(0);
                $table->double('grn_qty')->default(0);
                $table->double('sold_items')->default(0);
                $table->double('issued_qty')->default(0);
                $table->double('stock_returns')->default(0);
                $table->double('physical_dept_qty')->default(0);
                $table->double('dept_issued_qty')->default(0);
                $table->double('system_qty')->default(0);
                $table->double('dept_system_qty')->default(0);
                $table->double('physical_qty')->default(0);
                $table->date("date_recorded");
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
        Schema::dropIfExists('stock_movts');
    }
}
