<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->string('branch');
            $table->string('branch_code')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('country')->nullable();
            $table->string('town')->nullable();
            $table->string('kra_pin')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->string('details')->nullable();
            $table->string('till_no')->nullable();
            $table->string('kitchen_printer')->nullable();
            $table->string('bill_printer')->nullable();
            $table->string('invoice_printer')->nullable();
            $table->integer('account_id')->nullable();
            $table->enum('print_type', ['dialogue', 'server'])->default('dialogue');
            $table->boolean('enforce_pos_qty')->default(0);


            $table->string('fisc_api')->nullable();
            $table->string('cu_ip')->nullable();
            $table->integer('cu_port')->nullable();
            $table->string('cu_password')->nullable();
            $table->string('cu_serial')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });


        DB::statement('INSERT INTO `branches` (`id`, `branch`, `details`, `deleted_at`, `created_at`, `updated_at`, `business_name`, `branch_code`, `logo`, `address`, `postal_address`, `country`, `town`, `kra_pin`, `phone_no`, `email`, `till_no`, `kitchen_printer`, `bill_printer`, `invoice_printer`, `enforce_pos_qty`) VALUES
        (1, "Nairobi", NULL, NULL, NULL, "2023-07-12 19:07:12", "XYZ", NULL, "1689188832.png", "Parklands", "PO BOX  -00100-34", "Kenya", "Nairobi", "00000", "254 000 000", "admin@gmail.com", "00000", "POS80 Printer", "POS80 Printer", "POS80 Printer", 1)
       ');
    }




    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branches');
    }
}
