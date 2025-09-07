<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('asset_category_id')->constrained('asset_categories')->onDelete('NO ACTION');
            $table->string('asset_code')->nullable();
            $table->string('asset_name')->nullable();
            $table->double('qty')->default(0);
            $table->string('model_name')->nullable();
            $table->string('serial_no')->nullable();
            $table->date('purchase_date');
            $table->double('purchase_price')->default(0);
            $table->string('purchase_type')->nullable();
            $table->double('monthly_depreciation')->default(0);
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
        Schema::dropIfExists('assets');
    }
}
