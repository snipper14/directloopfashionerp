<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectRetailsCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('direct_retails_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->string('credit_no')->nullable();
            $table->string('details')->nullable();
            $table->enum('status',['progress','completed'])->nullable();
            $table->date('credit_date');
            $table->double('selling_price')->default(0);
            $table->double('qty')->default(0);
            $table->double('row_total')->default(0);
            $table->double('row_vat')->default(0);
            $table->string("image")->nullable();
            $table->integer('deleted_by')->nullable();
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
        Schema::dropIfExists('direct_retails_credits');
    }
}
