<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEtimsOnPOSCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('p_o_s_credits', function (Blueprint $table) {
            $table->string('etims_date')->nullable();
            $table->time('etims_time')->nullable();
            $table->dateTime("etims_timestamp")->nullable();
            $table->string('trader_invoice_number')->nullable();
            $table->string('digitax_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('sale_detail_url')->nullable();
            $table->string('etims_customer_name')->nullable();
            $table->string('etims_customer_pin')->nullable();

            // Sales tax summary columns
            $table->decimal('taxable_amount_a', 10, 2)->default(0);
            $table->decimal('taxable_amount_b', 10, 2)->default(0);
            $table->decimal('taxable_amount_c', 10, 2)->default(0);
            $table->decimal('taxable_amount_d', 10, 2)->default(0);
            $table->decimal('taxable_amount_e', 10, 2)->default(0);
            $table->decimal('tax_rate_a', 5, 2)->default(0);
            $table->decimal('tax_rate_b', 5, 2)->default(0);
            $table->decimal('tax_rate_c', 5, 2)->default(0);
            $table->decimal('tax_rate_d', 5, 2)->default(0);
            $table->decimal('tax_rate_e', 5, 2)->default(0);
            $table->decimal('tax_amount_a', 10, 2)->default(0);
            $table->decimal('tax_amount_b', 10, 2)->default(0);
            $table->decimal('tax_amount_c', 10, 2)->default(0);
            $table->decimal('tax_amount_d', 10, 2)->default(0);
            $table->decimal('tax_amount_e', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('p_o_s_credits', function (Blueprint $table) {
            $table->dropColumn([
                'etims_timestamp',
                'etims_date',
                'etims_time',
                'trader_invoice_number',
                'digitax_id',
                'serial_number',
                'receipt_number',
                'sale_detail_url',
                'etims_customer_name',
                'etims_customer_pin',
                'taxable_amount_a',
                'taxable_amount_b',
                'taxable_amount_c',
                'taxable_amount_d',
                'taxable_amount_e',
                'tax_rate_a',
                'tax_rate_b',
                'tax_rate_c',
                'tax_rate_d',
                'tax_rate_e',
                'tax_amount_a',
                'tax_amount_b',
                'tax_amount_c',
                'tax_amount_d',
                'tax_amount_e',
            ]);
        });
    }
}
