<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->text('teaching_functions')->nullable()->after('scientific_communities');
            $table->text('current_research_functions')->nullable()->after('teaching_functions');
            $table->string('personal_photo_path')->nullable()->after('current_research_functions');
            $table->string('principal_authority_name')->nullable()->after('rector_name');
            $table->unsignedSmallInteger('foundation_year')->nullable()->after('principal_authority_name');
            $table->string('institution_logo_path')->nullable()->after('foundation_year');
            $table->string('requester_name')->nullable()->after('institution_logo_path');
            $table->string('requester_position')->nullable()->after('requester_name');
            $table->string('requester_email')->nullable()->unique()->after('requester_position');
        });
        Schema::table('subscriptions', fn (Blueprint $table) => $table->dropUnique(['ruc']));
        Schema::table('subscriptions', fn (Blueprint $table) => $table->dropColumn(['ruc', 'rector_name']));
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropUnique(['requester_email']);
            $table->dropColumn([
                'teaching_functions', 'current_research_functions', 'personal_photo_path',
                'principal_authority_name', 'foundation_year', 'institution_logo_path',
                'requester_name', 'requester_position', 'requester_email',
            ]);
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('ruc', 30)->nullable()->unique();
            $table->string('rector_name')->nullable();
        });
    }
};
