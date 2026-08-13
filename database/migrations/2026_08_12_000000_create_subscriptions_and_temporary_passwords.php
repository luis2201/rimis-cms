<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();
            $table->string('status', 30)->default('submitted')->index();
            $table->string('email')->unique();
            $table->string('first_names')->nullable();
            $table->string('last_names')->nullable();
            $table->string('national_id', 30)->nullable()->unique();
            $table->string('orcid', 30)->nullable();
            $table->string('undergraduate_title')->nullable();
            $table->text('postgraduate_titles')->nullable();
            $table->json('research_areas')->nullable();
            $table->string('other_research_area')->nullable();
            $table->text('scientific_communities')->nullable();
            $table->text('research_activity')->nullable();
            $table->string('country');
            $table->string('city');
            $table->string('contact_phone', 30)->nullable()->unique();
            $table->string('institution_name')->nullable();
            $table->string('ruc', 30)->nullable()->unique();
            $table->string('rector_name')->nullable();
            $table->string('institution_type', 30)->nullable();
            $table->string('other_institution_type')->nullable();
            $table->string('main_phone', 30)->nullable()->unique();
            $table->string('mobile_phone', 30)->nullable()->unique();
            $table->string('public_slug')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('comments')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_histories');
        Schema::dropIfExists('subscriptions');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('must_change_password'));
    }
};
