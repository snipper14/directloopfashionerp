<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMixedStockMovtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mixed_stock_movts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');

            $table->double('opening_stock')->default(0);

            $table->double('grn_qty')->default(0);
            $table->double('sold_items')->default(0);
            $table->double('issued_qty')->default(0);

            $table->double('system_qty')->default(0);
            $table->double('closing_stock')->default(0);
            
            $table->double('physical_qty')->default(0);
            $table->date("date_recorded");
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
        Schema::dropIfExists('mixed_stock_movts');
    }
}
