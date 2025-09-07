<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequistionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requistions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->double('qty',15,2)->unsigned()->default(0)->nullable();
            $table->double('unit_price',15,2)->unsigned()->default(0)->nullable();
            
            $table->double('qty_issued',15,2)->unsigned()->default(0)->nullable();
            $table->dateTime('date_due');
            $table->enum('priority',['high','low','normal'])->default('normal');
            $table->enum('product_type',['assembly','single'])->default('single');
            $table->integer('approved')->default('0');
            $table->enum('status',['progress','complete'])->default('progress');
            $table->string('req_id')->nullable();
            $table->string('details')->nullable();
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
        Schema::dropIfExists('requistions');
    }
}
