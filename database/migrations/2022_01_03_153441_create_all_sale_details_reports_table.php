<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllSaleDetailsReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('all_sale_details_reports')) {
            Schema::create('all_sale_details_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
                $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
                $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
                $table->integer('cashier_id')->unsigned()->nullable();
                $table->integer('accompaniment_id')->unsigned()->nullable();
                $table->integer('product_category_id')->unsigned()->nullable();
                $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
               
                $table->integer('department_id')->nullable();
                $table->double('qty', 15, 2)->default(0);
                $table->double('price', 15, 2)->default(0);
                $table->double('row_total', 15, 2)->default(0);
                $table->double('row_vat', 15, 2)->default(0);
                $table->double('vat_rate', 15, 2)->default(0);
                $table->double('cost_price', 15, 2)->default(0);
                $table->double('cost_total', 15, 2)->default(0);
                $table->string('notes')->nullable();
                $table->date('order_date');
                $table->string('order_no')->nullable();
                $table->integer('customer_id')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('order_type')->nullable();
                $table->string('batch_no')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all_sale_details_reports');
    }
}
