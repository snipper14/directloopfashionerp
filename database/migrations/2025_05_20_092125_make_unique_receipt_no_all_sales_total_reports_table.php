<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUniqueReceiptNoAllSalesTotalReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_sales_total_reports', function (Blueprint $table) {
            //   $table->unique('receipt_no');
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
         //    $table->dropUnique(['receipt_no']);
        });
    }
}
