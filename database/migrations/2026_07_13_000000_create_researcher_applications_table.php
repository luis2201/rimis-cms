<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('researcher_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->text('motivation')->nullable();
            $table->text('experience_summary')->nullable();
            $table->text('expected_contribution')->nullable();
            $table->json('profile_snapshot')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('researcher_applications');
    }
};
