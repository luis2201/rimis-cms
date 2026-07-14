<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('researcher_application_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('researcher_application_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30)->index();
            $table->text('comments')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['researcher_application_id', 'created_at'], 'researcher_app_history_application_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('researcher_application_history');
    }
};
