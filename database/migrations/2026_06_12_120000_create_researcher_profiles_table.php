<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('researcher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('country', 100);
            $table->string('salutation', 30);
            $table->string('academic_title', 150);
            $table->string('profession', 150);
            $table->string('research_area', 150);
            $table->string('institution', 255);
            $table->string('phone', 30);
            $table->string('cv_path');
            $table->string('cv_original_name');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('researcher_profiles');
    }
};
