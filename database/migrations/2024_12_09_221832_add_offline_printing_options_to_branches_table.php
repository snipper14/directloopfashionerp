<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfflinePrintingOptionsToBranchesTable extends Migration
{
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            // Drop the column first (necessary to modify an enum)
            $table->dropColumn('print_type');
        });

        Schema::table('branches', function (Blueprint $table) {
            // Recreate the column with the updated enum values
            $table->enum('print_type', ['dialogue', 'server', 'offline'])->default('dialogue');
        });
    }

    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            // Rollback: Drop the updated column
            $table->dropColumn('print_type');
        });

        Schema::table('branches', function (Blueprint $table) {
            // Recreate the original column
            $table->enum('print_type', ['dialogue', 'server'])->default('dialogue');
        });
    }
}
