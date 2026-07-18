<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['events', 'bulletins', 'calls'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->string('origin', 20)->default('staff')->index();
                $table->string('review_status', 30)->default('not_required')->index();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('review_started_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_notes')->nullable();
            });
        }

        Schema::table('events', function (Blueprint $table) {
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->string('attachment_mime_type')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
        });

        Schema::create('content_review_history', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable');
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('comments')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_review_history');
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_original_name', 'attachment_mime_type', 'attachment_size']);
        });
        foreach (['events', 'bulletins', 'calls'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('reviewed_by');
                $table->dropColumn(['origin', 'review_status', 'submitted_at', 'review_started_at', 'reviewed_at', 'review_notes']);
            });
        }
    }
};
