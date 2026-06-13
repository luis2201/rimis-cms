<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('featured_image_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('modality', 20)->default('in_person');
            $table->string('location')->nullable();
            $table->string('organizer')->nullable();
            $table->string('responsible_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'starts_at']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
