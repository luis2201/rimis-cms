<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // Información general
            $table->string('site_name')->default('RIMIS');
            $table->string('site_description')->nullable();
            $table->string('site_slogan')->nullable();

            // Identidad visual
            $table->string('logo')->nullable();
            $table->string('logo_white')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color', 20)->default('#0d6efd');
            $table->string('secondary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();

            // Contacto
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();

            // Redes sociales
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('x_twitter')->nullable();
            $table->string('tiktok')->nullable();

            // Footer
            $table->text('footer_text')->nullable();
            $table->text('copyright_text')->nullable();

            // SEO global
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_image')->nullable();

            // Scripts opcionales
            $table->text('header_scripts')->nullable();
            $table->text('footer_scripts')->nullable();

            // Estado
            $table->boolean('maintenance_mode')->default(false);
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_settings');
    }
};
