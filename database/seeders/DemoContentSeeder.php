<?php

namespace Database\Seeders;

use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Models\Event;
use App\Models\MediaFile;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->prepareStorageDirectories();

        $author = User::role('ADMINISTRADOR')->first() ?? User::firstOrFail();
        $images = MediaFile::where('file_type', 'image')->where('status', true)->pluck('id')->values();

        $categories = collect([
            ['name' => 'Investigación', 'description' => 'Avances y proyectos científicos de la red.'],
            ['name' => 'Innovación', 'description' => 'Tecnología, transferencia y nuevas soluciones.'],
            ['name' => 'Comunidad RIMIS', 'description' => 'Historias y colaboración entre investigadores.'],
        ])->mapWithKeys(function (array $data) {
            $category = NewsCategory::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                $data + ['is_active' => true]
            );

            return [$data['name'] => $category];
        });

        $tags = collect(['Ciencia abierta', 'Inteligencia artificial', 'Sostenibilidad', 'Colaboración'])
            ->map(fn (string $name) => NewsTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]));

        $newsItems = [
            [
                'title' => 'RIMIS impulsa una agenda multidisciplinaria para el desarrollo sostenible',
                'category' => 'Investigación',
                'excerpt' => 'Investigadores de distintas áreas articulan una agenda común orientada a desafíos ambientales y sociales.',
                'content' => '<p>La Red de Investigación Multidisciplinaria consolidó una agenda de trabajo que conecta ciencia, tecnología y participación ciudadana.</p><h2>Conocimiento con propósito</h2><p>Los equipos desarrollarán iniciativas conjuntas con impacto medible, intercambio de datos y participación de nuevos investigadores.</p>',
            ],
            [
                'title' => 'Nuevas herramientas de inteligencia artificial fortalecen el análisis científico',
                'category' => 'Innovación',
                'excerpt' => 'La red explora metodologías responsables para acelerar el procesamiento y comprensión de datos.',
                'content' => '<p>La inteligencia artificial abre nuevas posibilidades para el análisis científico, siempre que se aplique con criterios éticos, transparencia y supervisión humana.</p><p>RIMIS promueve espacios de formación y experimentación responsable para sus investigadores.</p>',
            ],
            [
                'title' => 'Investigadores jóvenes encuentran nuevas rutas de colaboración',
                'category' => 'Comunidad RIMIS',
                'excerpt' => 'Mentorías y encuentros temáticos permiten conectar experiencias y desarrollar proyectos compartidos.',
                'content' => '<p>La colaboración entre investigadores en distintas etapas de su carrera fortalece la calidad y pertinencia de los proyectos.</p><p>Las nuevas rutas de mentoría facilitarán conexiones entre disciplinas, instituciones y territorios.</p>',
            ],
            [
                'title' => 'Ciencia abierta: compartir resultados para multiplicar su impacto',
                'category' => 'Investigación',
                'excerpt' => 'RIMIS promueve prácticas abiertas para ampliar el acceso al conocimiento y favorecer su reutilización.',
                'content' => '<p>La ciencia abierta facilita que datos, metodologías y resultados puedan ser consultados y reutilizados por nuevas comunidades.</p><p>La red trabaja en lineamientos para publicaciones accesibles y repositorios de investigación.</p>',
            ],
            [
                'title' => 'Tecnología y territorio se conectan en nuevos proyectos de innovación',
                'category' => 'Innovación',
                'excerpt' => 'Equipos multidisciplinarios diseñan soluciones a partir de necesidades identificadas en comunidades.',
                'content' => '<p>Los proyectos conectan capacidades tecnológicas con conocimiento local para construir respuestas sostenibles y pertinentes.</p><p>La participación territorial será una parte central del proceso de investigación.</p>',
            ],
            [
                'title' => 'La red amplía sus espacios de intercambio académico',
                'category' => 'Comunidad RIMIS',
                'excerpt' => 'Seminarios, diálogos y publicaciones fortalecerán la circulación de ideas entre miembros.',
                'content' => '<p>Durante los próximos meses, RIMIS desarrollará una programación permanente de encuentros académicos y espacios de divulgación.</p>',
            ],
        ];

        foreach ($newsItems as $index => $data) {
            $news = News::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'user_id' => $author->id,
                    'category_id' => $categories[$data['category']]->id,
                    'featured_image_id' => $this->imageId($images, $index + 1),
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'status' => News::STATUS_PUBLISHED,
                    'is_featured' => $index === 0,
                    'published_at' => now()->subDays($index + 1),
                    'seo_index' => true,
                ]
            );
            $news->tags()->sync($tags->slice($index % 3, 2)->pluck('id')->all());
        }

        $events = [
            ['title' => 'Foro RIMIS: investigación para ciudades sostenibles', 'days' => 8, 'hours' => 3, 'modality' => Event::MODALITY_HYBRID, 'location' => 'Auditorio principal y transmisión virtual'],
            ['title' => 'Taller de escritura y publicación científica', 'days' => 18, 'hours' => 4, 'modality' => Event::MODALITY_VIRTUAL, 'location' => 'Plataforma Zoom'],
            ['title' => 'Encuentro de investigadores emergentes', 'days' => 29, 'hours' => 5, 'modality' => Event::MODALITY_IN_PERSON, 'location' => 'Centro de innovación ITSŪP'],
            ['title' => 'Seminario sobre inteligencia artificial responsable', 'days' => 42, 'hours' => 3, 'modality' => Event::MODALITY_HYBRID, 'location' => 'Sala de conferencias y transmisión virtual'],
            ['title' => 'Diálogo multidisciplinario sobre ciencia abierta', 'days' => 57, 'hours' => 2, 'modality' => Event::MODALITY_VIRTUAL, 'location' => 'Microsoft Teams'],
        ];

        foreach ($events as $index => $data) {
            $startsAt = now()->addDays($data['days'])->setTime(9 + ($index % 3), 0);
            Event::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'user_id' => $author->id,
                    'featured_image_id' => $this->imageId($images, $index + 2),
                    'title' => $data['title'],
                    'summary' => 'Un espacio para conectar perspectivas, compartir experiencias y generar nuevas colaboraciones científicas.',
                    'description' => '<p>Este encuentro reunirá a investigadores, docentes y profesionales interesados en intercambiar conocimiento y construir nuevas iniciativas.</p><h2>Temas centrales</h2><ul><li>Colaboración multidisciplinaria</li><li>Metodologías y experiencias</li><li>Oportunidades de trabajo conjunto</li></ul>',
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addHours($data['hours']),
                    'modality' => $data['modality'],
                    'location' => $data['location'],
                    'organizer' => 'Red de Investigación Multidisciplinaria RIMIS',
                    'responsible_name' => 'Coordinación académica RIMIS',
                    'contact_email' => 'info@itsup.edu.ec',
                    'status' => Event::STATUS_PUBLISHED,
                    'published_at' => now()->subDays($index),
                ]
            );
        }

        $sourcePdf = Bulletin::whereNotNull('pdf_path')->first()?->pdf_path;
        $calls = [
            ['title' => 'Fondo semilla para proyectos multidisciplinarios 2026', 'open' => -7, 'close' => 35, 'registration' => true],
            ['title' => 'Convocatoria para investigadores jóvenes RIMIS', 'open' => -3, 'close' => 48, 'registration' => true],
            ['title' => 'Programa de movilidad y colaboración académica', 'open' => 12, 'close' => 62, 'registration' => true],
            ['title' => 'Reconocimiento a iniciativas de ciencia abierta', 'open' => -40, 'close' => -8, 'registration' => false],
        ];

        foreach ($calls as $index => $data) {
            $slug = Str::slug($data['title']);
            $pdfPath = "calls/demo-{$slug}.pdf";
            $this->ensurePdf($pdfPath, $sourcePdf);
            CallForProposal::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $author->id,
                    'featured_image_id' => $this->imageId($images, $index + 3),
                    'title' => $data['title'],
                    'summary' => 'Una oportunidad para fortalecer capacidades, desarrollar nuevas ideas y ampliar la colaboración científica.',
                    'description' => '<p>La convocatoria está dirigida a investigadores y equipos interesados en desarrollar propuestas de impacto multidisciplinario.</p><h2>Información general</h2><p>Consulta las bases PDF para conocer requisitos, criterios de evaluación y calendario completo.</p>',
                    'opens_at' => now()->addDays($data['open'])->startOfDay(),
                    'closes_at' => now()->addDays($data['close'])->endOfDay(),
                    'bases_pdf_path' => $pdfPath,
                    'bases_pdf_original_name' => "bases-{$slug}.pdf",
                    'bases_pdf_size' => Storage::disk('local')->size($pdfPath),
                    'registration_enabled' => $data['registration'],
                    'registration_url' => $data['registration'] ? 'https://forms.google.com/' : null,
                    'status' => CallForProposal::STATUS_PUBLISHED,
                    'published_at' => now()->subDays($index + 1),
                ]
            );
        }

        $bulletins = [
            ['title' => 'Boletín RIMIS · Horizontes de investigación', 'description' => 'Proyectos, hallazgos y nuevas conexiones de la comunidad científica.'],
            ['title' => 'Boletín RIMIS · Ciencia y territorio', 'description' => 'Experiencias que vinculan investigación, innovación y necesidades territoriales.'],
            ['title' => 'Boletín RIMIS · Especial ciencia abierta', 'description' => 'Prácticas, herramientas y reflexiones para compartir conocimiento.'],
            ['title' => 'Boletín RIMIS · Innovación multidisciplinaria', 'description' => 'Soluciones desarrolladas desde el encuentro entre distintas disciplinas.'],
        ];

        foreach ($bulletins as $index => $data) {
            $slug = Str::slug($data['title']);
            $pdfPath = "bulletins/demo-{$slug}.pdf";
            $this->ensurePdf($pdfPath, $sourcePdf);
            Bulletin::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $author->id,
                    'cover_image_id' => $this->imageId($images, $index + 1),
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'pdf_path' => $pdfPath,
                    'pdf_original_name' => "{$slug}.pdf",
                    'pdf_size' => Storage::disk('local')->size($pdfPath),
                    'status' => Bulletin::STATUS_PUBLISHED,
                    'published_at' => now()->subMonths($index)->subDays(2),
                ]
            );
        }
    }

    private function imageId($images, int $offset): ?int
    {
        return $images->isEmpty() ? null : $images[$offset % $images->count()];
    }

    private function ensurePdf(string $path, ?string $sourcePdf): void
    {
        if (Storage::disk('local')->exists($path)) {
            return;
        }

        if ($sourcePdf && Storage::disk('local')->exists($sourcePdf)) {
            Storage::disk('local')->copy($sourcePdf, $path);

            return;
        }

        Storage::disk('local')->put($path, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");
    }

    private function prepareStorageDirectories(): void
    {
        foreach (['calls', 'bulletins'] as $directory) {
            $path = storage_path('app/'.$directory);
            File::ensureDirectoryExists($path, 0775);
            chmod($path, 0775);
        }
    }
}
