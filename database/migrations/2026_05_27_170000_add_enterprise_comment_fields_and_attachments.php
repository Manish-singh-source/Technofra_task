<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('task_comments')) {
            Schema::table('task_comments', function (Blueprint $table) {
                if (! Schema::hasColumn('task_comments', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('task_id')->constrained('task_comments')->nullOnDelete();
                    $table->index('parent_id');
                }

                if (! Schema::hasColumn('task_comments', 'mentions')) {
                    $table->json('mentions')->nullable()->after('comment');
                }

                if (! Schema::hasColumn('task_comments', 'edited_at')) {
                    $table->timestamp('edited_at')->nullable()->after('mentions');
                }

                if (! Schema::hasColumn('task_comments', 'edit_history')) {
                    $table->json('edit_history')->nullable()->after('edited_at');
                }
            });
        }

        if (! Schema::hasTable('task_comment_attachments')) {
            Schema::create('task_comment_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_comment_id')->constrained('task_comments')->cascadeOnDelete();
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamps();

                $table->index('task_comment_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comment_attachments');

        if (Schema::hasTable('task_comments')) {
            Schema::table('task_comments', function (Blueprint $table) {
                if (Schema::hasColumn('task_comments', 'edit_history')) {
                    $table->dropColumn('edit_history');
                }

                if (Schema::hasColumn('task_comments', 'edited_at')) {
                    $table->dropColumn('edited_at');
                }

                if (Schema::hasColumn('task_comments', 'mentions')) {
                    $table->dropColumn('mentions');
                }

                if (Schema::hasColumn('task_comments', 'parent_id')) {
                    $table->dropConstrainedForeignId('parent_id');
                }
            });
        }
    }
};
