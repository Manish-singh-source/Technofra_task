<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seo_activities', function (Blueprint $table) {
            $table->string('evidence_url', 2048)->nullable()->after('work_notes');
        });
        Schema::table('seo_audit_findings', function (Blueprint $table) {
            $table->text('resolution_notes')->nullable()->after('recommended_solution');
            $table->string('evidence_url', 2048)->nullable()->after('resolution_notes');
        });
        Schema::table('seo_technical_issues', function (Blueprint $table) {
            $table->text('resolution_notes')->nullable()->after('description');
            $table->string('evidence_url', 2048)->nullable()->after('resolution_notes');
        });
        Schema::table('seo_on_page_optimizations', function (Blueprint $table) {
            $table->string('evidence_url', 2048)->nullable()->after('internal_link_changes');
        });
    }

    public function down(): void
    {
        Schema::table('seo_on_page_optimizations', fn (Blueprint $table) => $table->dropColumn('evidence_url'));
        Schema::table('seo_technical_issues', fn (Blueprint $table) => $table->dropColumns(['resolution_notes', 'evidence_url']));
        Schema::table('seo_audit_findings', fn (Blueprint $table) => $table->dropColumns(['resolution_notes', 'evidence_url']));
        Schema::table('seo_activities', fn (Blueprint $table) => $table->dropColumn('evidence_url'));
    }
};
