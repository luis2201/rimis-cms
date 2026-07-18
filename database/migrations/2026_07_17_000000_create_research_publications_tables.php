<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('research_publications', function(Blueprint $t){
   $t->id(); $t->foreignId('user_id')->constrained()->restrictOnDelete(); $t->string('title',500)->index(); $t->string('slug')->unique(); $t->longText('abstract')->nullable();
   $t->string('publication_type')->nullable()->index(); $t->date('publication_date')->nullable(); $t->unsignedSmallInteger('year')->nullable()->index(); $t->string('research_area')->nullable()->index(); $t->string('research_line')->nullable()->index(); $t->string('institution')->nullable()->index();
   $t->string('journal_name')->nullable(); $t->string('publisher')->nullable(); $t->string('volume',50)->nullable(); $t->string('issue',50)->nullable(); $t->string('pages',100)->nullable(); $t->string('doi')->nullable()->index(); $t->string('issn',50)->nullable(); $t->string('isbn',50)->nullable(); $t->string('external_url',2048)->nullable(); $t->json('keywords')->nullable(); $t->text('citation_text')->nullable();
   $t->string('pdf_path')->nullable(); $t->string('pdf_original_name')->nullable(); $t->string('pdf_mime_type')->nullable(); $t->unsignedBigInteger('pdf_size')->nullable(); $t->foreignId('cover_image_id')->nullable()->constrained('media_files')->nullOnDelete(); $t->boolean('pdf_distribution_authorized')->default(false); $t->boolean('pdf_public')->default(false); $t->string('license_type')->nullable();
   $t->string('seo_title')->nullable(); $t->string('seo_description',500)->nullable(); $t->string('origin',20)->default('researcher')->index(); $t->string('review_status',30)->default('draft')->index(); $t->string('status',20)->default('draft')->index();
   $t->timestamp('submitted_at')->nullable(); $t->timestamp('review_started_at')->nullable(); $t->timestamp('reviewed_at')->nullable(); $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); $t->text('review_notes')->nullable(); $t->timestamp('published_at')->nullable()->index(); $t->timestamps();
  });
  Schema::create('research_publication_authors',function(Blueprint $t){ $t->id(); $t->foreignId('research_publication_id')->constrained()->cascadeOnDelete(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->string('author_name'); $t->string('institution')->nullable(); $t->string('orcid',30)->nullable(); $t->string('email')->nullable(); $t->unsignedSmallInteger('author_order'); $t->boolean('is_corresponding')->default(false); $t->timestamps(); $t->unique(['research_publication_id','author_order'],'research_publication_author_order_unique'); });
 }
 public function down(): void { Schema::dropIfExists('research_publication_authors'); Schema::dropIfExists('research_publications'); }
};
