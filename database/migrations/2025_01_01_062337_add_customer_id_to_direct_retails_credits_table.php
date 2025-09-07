<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToDirectRetailsCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_retails_credits', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained('customers')->onDelete('NO ACTION');
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
            $table->dropColumn(['customer_id']);
        });
    }
}
