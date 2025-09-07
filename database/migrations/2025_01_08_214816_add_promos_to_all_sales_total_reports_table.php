<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromosToAllSalesTotalReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_sales_total_reports', function (Blueprint $table) {
            $table->double('discount_pay')->default(0);
            $table->double('discount_percent')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('all_sales_total_reports', function (Blueprint $table) {
            $table->dropColumn(['discount_percent','discount_pay']);
        });
    }
}
