<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSplitProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('split_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('original_product_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('final_product_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('split_product_setting_id')->constrained('split_product_settings')->onDelete('NO ACTION');
            $table->double('qty_split');
            $table->double('final_qty');
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
        Schema::dropIfExists('split_products');
    }
}
