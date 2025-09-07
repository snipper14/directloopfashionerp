<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSplitProductSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('split_product_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_product_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->foreignId('final_product_id')->constrained('stocks')->onDelete('NO ACTION');
            $table->double('final_qty', 15, 2);
            $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
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
        Schema::dropIfExists('split_product_settings');
    }
}
