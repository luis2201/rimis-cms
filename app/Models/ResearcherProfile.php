<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ResearcherProfile extends Model
{
    use HasFactory;

    public const SALUTATIONS = ['Señor', 'Señora', 'Señorita', 'Doctor', 'Doctora', 'Profesor', 'Profesora'];

    public const COUNTRIES = [
        'Ecuador', 'Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia', 'Costa Rica',
        'Cuba', 'El Salvador', 'España', 'Estados Unidos', 'Guatemala', 'Honduras',
        'México', 'Nicaragua', 'Panamá', 'Paraguay', 'Perú', 'Puerto Rico',
        'República Dominicana', 'Uruguay', 'Venezuela', 'Otro',
    ];

    public const RESEARCH_AREAS = [
        'Ciencias agrícolas', 'Ciencias de la educación', 'Ciencias de la salud',
        'Ciencias económicas y administrativas', 'Ciencias naturales y exactas',
        'Ciencias sociales y humanidades', 'Derecho y ciencias políticas',
        'Ingeniería y tecnología', 'Medio ambiente y sostenibilidad',
        'Tecnologías de la información y comunicación', 'Otra',
    ];

    protected $fillable = [
        'country',
        'salutation',
        'academic_title',
        'profession',
        'research_area',
        'institution',
        'phone',
        'cv_path',
        'cv_original_name',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (ResearcherProfile $profile) {
            if ($profile->cv_path) {
                Storage::disk('local')->delete($profile->cv_path);
            }
        });
    }
}
