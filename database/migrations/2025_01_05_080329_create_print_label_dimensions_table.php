<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintLabelDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('print_label_dimensions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('bar_font_size')->default(16);
            $table->integer('bar_height')->default(100);
            $table->integer('bar_width')->default(2);
            $table->integer('wrapper_height')->default(220);
            $table->integer('wrapper_width')->default(195);
            $table->integer('item_description_fontsize')->default(18);
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
        Schema::dropIfExists('print_label_dimensions');
    }
}
