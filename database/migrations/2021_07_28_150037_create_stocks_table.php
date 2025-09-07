<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {

            $table->id();
           // $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('NO ACTION');
            $table->foreignId('unit_id')->constrained('units')->onDelete('NO ACTION');
            $table->foreignId('tax_dept_id')->constrained('tax_depts')->onDelete('NO ACTION');
            $table->string('name');
            $table->string('product_name');
            $table->enum('composition', ['aggregate', 'whole'])->default("whole");
            $table->string('code')->nullable();
            $table->string('image')->nullable();
            $table->double('qty')->default(0);
            $table->double('reorder_qty')->default(0);
            $table->double('selling_price', 15, 2)->default(0);
            $table->double('wholesale_price', 15, 2)->default(0);
            $table->double('cost_price', 15, 2)->default(0);
          
            $table->string('description')->nullable();
            $table->boolean('show_img')->default(1);
            $table->boolean('isKitchenProduct')->default(0);
            $table->boolean('isDrinkProduct')->default(0);

            $table->integer('department_id')->nullable()->unsigned();
            $table->double('purchase_price')->default(0);
            $table->double('store_qty')->default(0);
            $table->double('main_store_qty')->default(0);
            $table->double('mapping_value')->default(1);
            $table->boolean('is_active')->default(1);
            $table->string('menu_description')->nullable();
            $table->enum('isSync', ['0', '1'])->default("1");
            $table->enum('inventory_type', ['inventory', 'non_inventory'])->default("inventory");
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
        Schema::dropIfExists('stocks');
    }
}
