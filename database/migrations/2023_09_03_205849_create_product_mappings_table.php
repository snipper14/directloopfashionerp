<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('element_id')->constrained('stocks')->onDelete('NO ACTION');
      
            $table->double('qty')->default(0);
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
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
        Schema::dropIfExists('product_mappings');
    }
}
