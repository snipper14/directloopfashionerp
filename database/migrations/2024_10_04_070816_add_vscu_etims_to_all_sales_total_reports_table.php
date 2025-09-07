<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVscuEtimsToAllSalesTotalReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_sales_total_reports', function (Blueprint $table) {
            $table->string('signature')->nullable();
            $table->string('scdcId')->nullable();
            $table->string('scuReceiptDate')->nullable();
            $table->string('scuReceiptNo')->nullable();
            $table->string('invoiceVerificationUrl')->nullable();
            $table->string('exchangeRate')->nullable();
            $table->string('totalTaxableAmount')->nullable();
            $table->string('totalTaxAmount')->nullable();
            $table->string('paymentType')->nullable();
            $table->string('salesTypeCode')->nullable();
            $table->string('receiptTypeCode')->nullable();
            $table->string('salesStatusCode')->nullable();
            $table->string('internalData')->nullable();
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
            $table->dropColumn([
                'signature',
                'scdcId',
                'scuReceiptDate',
                'scuReceiptNo',
                'invoiceVerificationUrl',
                'exchangeRate',

                'totalTaxableAmount',
                'totalTaxAmount',
                'paymentType',
                'salesTypeCode',
                'receiptTypeCode',
                'salesStatusCode',
                'internalData',

            ]);
        });
    }
}
