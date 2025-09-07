<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePOSCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_o_s_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('all_sale_details_report_id')->constrained('all_sale_details_reports')->onDelete('NO ACTION');
            
            $table->double('receipt_qty', 15, 2)->default(0);
            $table->double('qty', 15, 2)->default(0);
            $table->double('selling_price', 15, 2)->default(0);
            $table->double('row_total', 15, 2)->default(0);
            $table->double('row_vat', 15, 2)->default(0);
            $table->double('credit_sp', 15, 2)->default(0);
            $table->double('credit_vat', 15, 2)->default(0);
            $table->double('credit_amount', 15, 2)->default(0);
           
            $table->date('credit_date');
            $table->string('receipt_no')->nullable();
            $table->string('credit_no')->nullable();
            $table->string('cu_invoice_no')->nullable();
            
            $table->double('tax_rate', 15, 2)->default(0);
            $table->boolean('deduct_stock');
            
            $table->enum('status',['completed','pending'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('p_o_s_credits');
    }
}
