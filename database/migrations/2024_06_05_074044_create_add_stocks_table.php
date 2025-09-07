<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('add_stocks')) {
            Schema::create('add_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('NO ACTION');
                $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
                $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');

                $table->double('qty');

                $table->foreignId('stock_id')->constrained('stocks')->onDelete('NO ACTION');
                $table->string('entry_code')->nullable();
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
        Schema::dropIfExists('add_stocks');
    }
}
