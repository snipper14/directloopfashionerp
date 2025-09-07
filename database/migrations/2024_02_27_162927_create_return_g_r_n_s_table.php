<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnGRNSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_g_r_n_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('purchase_order_receivable_id')->constrained('purchase_order_receivables')->onDelete('NO ACTION');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('NO ACTION');
            $table->double('unit_price', 15, 2)->default(0);
            $table->double('qty', 15, 2)->default(0);
            $table->double('tax_rate', 15, 2)->default(0);
            $table->double('tax_amount', 15, 2)->default(0);
            $table->double('sub_total', 15, 2)->default(0);
            $table->date("return_date");
            $table->string("returns_conditions")->nullable();
            $table->string("return_reasons")->nullable();
            $table->string("return_code")->nullable();
            $table->string("delivery_no")->nullable();
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
        Schema::dropIfExists('return_g_r_n_s');
    }
}
