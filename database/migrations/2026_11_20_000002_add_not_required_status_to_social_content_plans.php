<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE social_content_plans MODIFY status ENUM('draft', 'pending_client_review', 'client_approved', 'changes_requested', 'not_required') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::table('social_content_plans')
            ->where('status', 'not_required')
            ->update(['status' => 'changes_requested']);

        DB::statement("ALTER TABLE social_content_plans MODIFY status ENUM('draft', 'pending_client_review', 'client_approved', 'changes_requested') NOT NULL DEFAULT 'draft'");
    }
};
