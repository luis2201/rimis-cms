<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ResearchPublicationAuthor extends Model { protected $fillable=['user_id','author_name','institution','orcid','email','author_order','is_corresponding']; protected $casts=['is_corresponding'=>'boolean']; public function publication():BelongsTo{return $this->belongsTo(ResearchPublication::class,'research_publication_id');} public function user():BelongsTo{return $this->belongsTo(User::class);} }
