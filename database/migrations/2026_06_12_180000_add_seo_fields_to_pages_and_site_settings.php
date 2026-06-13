<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('show_title');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->text('seo_keywords')->nullable()->after('seo_description');
            $table->string('seo_canonical_url')->nullable()->after('seo_keywords');
            $table->foreignId('seo_image_id')->nullable()->after('seo_canonical_url')->constrained('media_files')->nullOnDelete();
            $table->boolean('seo_index')->default(true)->after('seo_image_id');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('seo_index')->default(true)->after('og_image');
            $table->string('twitter_card', 40)->default('summary_large_image')->after('seo_index');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seo_image_id');
            $table->dropColumn(['seo_title', 'seo_description', 'seo_keywords', 'seo_canonical_url', 'seo_index']);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['seo_index', 'twitter_card']);
        });
    }
};
