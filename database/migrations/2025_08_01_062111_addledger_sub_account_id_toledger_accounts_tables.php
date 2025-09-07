<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddledgerSubAccountIdToledgerAccountsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_sub_account_id')->nullable()->after('category');
            $table->foreign('ledger_sub_account_id')->references('id')->on('ledger_sub_accounts')->onDelete('NO ACTION');
            $table->string('account_no')->nullable()->after('ledger_sub_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropForeign(['ledger_sub_account_id']);
            $table->dropColumn('ledger_sub_account_id');
            $table->dropColumn('account_no');
        });
    }
}
