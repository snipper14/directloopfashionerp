<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->string('show_pos')->default(0);
            $table->integer('branch_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('INSERT INTO `departments` (`id`, `department`, `details`, `deleted_at`, `created_at`, `updated_at`, `branch_id`, `user_id`, `show_pos`) VALUES
        (1, "ADMINISTRATION", NULL, NULL, NULL, "2021-11-22 16:10:53", 1, 1,1) ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
