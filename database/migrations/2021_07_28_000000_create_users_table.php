<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('NO ACTION');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('NO ACTION');
            $table->foreignId('role_id')->constrained('roles')->onDelete('NO ACTION');
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('other_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(
            "
            INSERT INTO `users` (`id`, `department_id`, `branch_id`, `role_id`, `name`, `first_name`, `other_name`, `phone`, `title`, `email`, `username`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 'Collins Abdalla', 'Kazungu', 'Kambi', '0724496455', NULL, 'kazungu@gmail.com', 'admin', '$2y$10$6n0rVA.4r9zT.4M1KQ74dOMUKbIHSyhIYowRsmuqXbptAlNbP.B2K', NULL, NULL, '2023-02-16 08:32:40', NULL)
            "
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
