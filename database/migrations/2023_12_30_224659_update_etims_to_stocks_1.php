<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEtimsToStocks1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->string('item_id')->nullable();
            $table->string('item_class_code')->nullable();
            $table->string('item_type_code')->nullable();
            $table->string('origin_nation_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('package_unit_code')->nullable();
            $table->string('quantity_unit_code')->nullable();
            $table->string('tax_type_code')->nullable();
            $table->decimal('default_unit_price', 10, 2)->nullable();
            $table->string('etims_id')->nullable();
            $table->boolean('is_stock_item')->default(true); // Assuming 'is_stock_item' is a boolean column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn([
                'item_name',
                'item_id',
                'item_class_code',
                'item_type_code',
                'origin_nation_code',
                'package_unit_code',
                'quantity_unit_code',
                'tax_type_code',
                'default_unit_price',
                'etims_id',
                'is_stock_item',
            ]);
        });
    }
}
