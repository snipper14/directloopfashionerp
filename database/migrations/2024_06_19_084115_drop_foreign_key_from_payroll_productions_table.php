<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeyFromPayrollProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_productions', function (Blueprint $table) {
           
          
            $table->dropForeign('payroll_productions_payroll_product_id_foreign'); // Drop the foreign key constraint
            $table->dropColumn('payroll_product_id');
            $table->double('production_cost')->default(0);
            $table->integer('stock_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_productions', function (Blueprint $table) {
            $table->foreignId('payroll_product_id')->constrained('payroll_products')->onDelete('NO ACTION')->name('payroll_productions_payroll_product_id_foreign');
              $table->dropColumn(['production_cost','stock_id']);
     
        });
    }
}
