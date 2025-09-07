<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEtimsDataToStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('etims_item_type_id')->nullable();
            $table->integer('etims_origin_country_id')->nullable();
            $table->integer('etims_item_packaging_code_id')->nullable();
            $table->integer('item_classification_id')->nullable();
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
            $table->dropIfExists('etims_item_type_id');
            $table->dropIfExists('etims_origin_country_id');
            $table->dropIfExists('etims_item_packaging_code_id');
            $table->dropIfExists('item_classification_id');
        });
    }
}
