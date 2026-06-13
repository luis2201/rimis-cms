<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('featured_image_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->string('bases_pdf_path');
            $table->string('bases_pdf_original_name');
            $table->unsignedBigInteger('bases_pdf_size')->nullable();
            $table->boolean('registration_enabled')->default(false);
            $table->string('registration_url', 2048)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'opens_at', 'closes_at']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
