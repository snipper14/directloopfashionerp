<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCardTerminalAccountIdAllSalesTotalReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_sales_total_reports', function (Blueprint $table) {
            $table->integer('card_terminal_account_id')->nullable();
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
            $table->dropColumn('card_terminal_account_id');
        });
    }
}
