<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleInfoToDirectRetailsCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_retails_credits', function (Blueprint $table) {
            $table->enum('entry_type',['original','replacement'])->default('original');
            $table->string('receipt_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('direct_retails_credits', function (Blueprint $table) {
            $table->dropColumn(['entry_type','receipt_no']);
        });
    }
}
