<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('researcher_profiles', function (Blueprint $table) {
            $table->string('public_slug')->nullable()->unique()->after('user_id');
            $table->text('public_bio')->nullable()->after('profession');
            $table->string('research_line')->nullable()->index()->after('research_area');
            $table->string('orcid', 30)->nullable()->after('research_line');
            $table->string('google_scholar_url', 2048)->nullable();
            $table->string('researchgate_url', 2048)->nullable();
            $table->string('linkedin_url', 2048)->nullable();
            $table->string('personal_website_url', 2048)->nullable();
            $table->foreignId('profile_photo_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->boolean('profile_public')->default(false)->index();
            $table->boolean('public_email')->default(false);
            $table->boolean('public_phone')->default(false);
            $table->boolean('public_institution')->default(true);
            $table->boolean('public_country')->default(true);
            $table->boolean('public_research_area')->default(true);
            $table->boolean('public_research_line')->default(true);
            $table->boolean('public_cv')->default(false);
            $table->boolean('publications_section_enabled')->default(true);
            $table->boolean('contributions_section_enabled')->default(true);
            $table->index('country');
            $table->index('institution');
            $table->index('research_area');
        });
    }

    public function down(): void
    {
        Schema::table('researcher_profiles', function (Blueprint $table) {
            $table->dropForeign(['profile_photo_id']);
            $table->dropIndex(['country']);
            $table->dropIndex(['institution']);
            $table->dropIndex(['research_area']);
            $table->dropIndex(['research_line']);
            $table->dropIndex(['profile_public']);
            $table->dropUnique(['public_slug']);
            $table->dropColumn(['public_slug','public_bio','research_line','orcid','google_scholar_url','researchgate_url','linkedin_url','personal_website_url','profile_photo_id','profile_public','public_email','public_phone','public_institution','public_country','public_research_area','public_research_line','public_cv','publications_section_enabled','contributions_section_enabled']);
        });
    }
};
