<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxDeptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_depts', function (Blueprint $table) {
            $table->id();
            $table->double('tax_rate',6,2);
            $table->string('tax_code');
             $table->double('formulae',15,8);
             $table->string('type')->nullable();
             $table->softDeletes();
            $table->timestamps();
        });

        // DB::statement(
        //     "
        //     INSERT INTO `tax_depts` (`id`, `tax_rate`, `tax_code`, `formulae`, `type`, `deleted_at`, `created_at`, `updated_at`) VALUES
        //     (1, 8.00, 'D', 0.00000000, NULL, NULL, '2023-02-07 12:03:46', '2023-02-07 12:03:46'),
        //     (2, 0.00, '', 0.00000000, NULL, NULL, '2023-02-07 12:03:46', '2023-06-09 13:17:06'),
        //     (3, 16.00, 'A', 0.00000000, NULL, NULL, '2023-02-07 12:03:46', '2023-06-09 13:17:04')
          
        //     "
        // );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_depts');
    }
}
