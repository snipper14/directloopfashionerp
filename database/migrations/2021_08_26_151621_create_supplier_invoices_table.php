<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('NO ACTION');
         
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->string('invoice_no');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->string("po_no")->nullable();
            $table->double('invoice_total',15,2);
            $table->double('unpaid_amount',15,2)->default(0);
            $table->double('paid_amount',15,2)->default(0);
            $table->date('invoice_date');
            $table->string('description')->nullable();
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
        Schema::dropIfExists('supplier_invoices');
    }
}
