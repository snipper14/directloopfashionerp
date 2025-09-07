<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryLogsTable extends Migration
{
     public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('stock_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('branch_id');
            $table->decimal('change_qty', 20, 6); // +ve or -ve
            $table->decimal('new_qty', 20, 6);    // qty after change
            $table->string('action_type', 20);    // create, update, increment, decrement, deleted
            $table->unsignedBigInteger('user_id')->nullable(); // from session variable
            $table->timestamps();

            $table->index(['stock_id','department_id','branch_id']);
            $table->index(['inventory_id']);
        });

        // CREATE TRIGGERs
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_inventory_insert;
            CREATE TRIGGER trg_inventory_insert
            AFTER INSERT ON inventories FOR EACH ROW
            BEGIN
                INSERT INTO inventory_logs
                    (inventory_id, stock_id, department_id, branch_id,
                     change_qty, new_qty, action_type, user_id, created_at, updated_at)
                VALUES
                    (NEW.id, NEW.stock_id, NEW.department_id, NEW.branch_id,
                     NEW.qty, NEW.qty,
                     'create',
                     @actor_id, NOW(), NOW());
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_inventory_update;
            CREATE TRIGGER trg_inventory_update
            AFTER UPDATE ON inventories FOR EACH ROW
            BEGIN
                IF (NEW.qty <> OLD.qty) THEN
                    INSERT INTO inventory_logs
                        (inventory_id, stock_id, department_id, branch_id,
                         change_qty, new_qty, action_type, user_id, created_at, updated_at)
                    VALUES
                        (NEW.id, NEW.stock_id, NEW.department_id, NEW.branch_id,
                         (NEW.qty - OLD.qty), NEW.qty,
                         CASE
                            WHEN NEW.qty > OLD.qty THEN 'increment'
                            WHEN NEW.qty < OLD.qty THEN 'decrement'
                            ELSE 'update'
                         END,
                         @actor_id, NOW(), NOW());
                END IF;
            END;
        ");

        // Optional: log hard deletes (SoftDeletes normally prevents this,
        // but in case of forceDelete)
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_inventory_delete;
            CREATE TRIGGER trg_inventory_delete
            AFTER DELETE ON inventories FOR EACH ROW
            BEGIN
                INSERT INTO inventory_logs
                    (inventory_id, stock_id, department_id, branch_id,
                     change_qty, new_qty, action_type, user_id, created_at, updated_at)
                VALUES
                    (OLD.id, OLD.stock_id, OLD.department_id, OLD.branch_id,
                     -OLD.qty, 0,
                     'deleted',
                     @actor_id, NOW(), NOW());
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_inventory_insert;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_inventory_update;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_inventory_delete;");
        Schema::dropIfExists('inventory_logs');
    }
}
