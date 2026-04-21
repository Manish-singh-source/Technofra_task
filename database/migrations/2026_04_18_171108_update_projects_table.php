<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('projects', 'customer_id')) {
            try {
                // Check if foreign key exists before dropping
                $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'projects' AND COLUMN_NAME = 'customer_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                
                if (!empty($constraints)) {
                    foreach ($constraints as $constraint) {
                        DB::statement('ALTER TABLE projects DROP FOREIGN KEY ' . $constraint->CONSTRAINT_NAME);
                    }
                }
            } catch (\Exception $e) {
                // Continue if constraint doesn't exist
            }
            
            // Modify the column to allow NULL
            try {
                DB::statement('ALTER TABLE projects MODIFY customer_id BIGINT UNSIGNED NULL');
            } catch (\Exception $e) {
                // Column might already be modified
            }
            
            // Add the new foreign key
            try {
                DB::statement('ALTER TABLE projects ADD CONSTRAINT projects_customer_id_foreign 
                    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL');
            } catch (\Exception $e) {
                // Constraint might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('projects', 'customer_id')) {
            try {
                // Check if foreign key exists before dropping
                $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'projects' AND COLUMN_NAME = 'customer_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                
                if (!empty($constraints)) {
                    foreach ($constraints as $constraint) {
                        DB::statement('ALTER TABLE projects DROP FOREIGN KEY ' . $constraint->CONSTRAINT_NAME);
                    }
                }
            } catch (\Exception $e) {
                // Continue if constraint doesn't exist
            }
            
            // Modify the column back to NOT NULL
            try {
                DB::statement('ALTER TABLE projects MODIFY customer_id BIGINT UNSIGNED NOT NULL');
            } catch (\Exception $e) {
                // Column might already be modified
            }
            
            // Add the original foreign key
            try {
                DB::statement('ALTER TABLE projects ADD CONSTRAINT projects_customer_id_foreign 
                    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE');
            } catch (\Exception $e) {
                // Constraint might already exist
            }
        }
    }
};
