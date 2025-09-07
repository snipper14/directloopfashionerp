<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllSalesTotalReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // if (!Schema::hasTable('all_sales_total_reports')) {
        Schema::create('all_sales_total_reports', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->string('receipt_no')->unique();

            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->integer('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->date('order_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->double('qty', 8, 1);
            $table->double('receipt_balance', 15, 2)->default(0);
            $table->double('receipt_total', 15, 2)->default(0);
            $table->double('total_vat', 15, 2)->default(0);
            $table->double('cash_pay', 15, 2)->default(0);
            $table->double('mpesa_pay', 15, 2)->default(0);
            $table->double('card_pay', 15, 2)->default(0);
            $table->double('credit_pay', 15, 2)->default(0);

            $table->string('payment_ref')->nullable();

            $table->softDeletes();

            $table->timestamps();
        });
    }
    //}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all_sales_total_reports');
    }
}
