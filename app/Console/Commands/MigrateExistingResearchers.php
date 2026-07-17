<?php

namespace App\Console\Commands;

use App\Models\ResearcherApplication;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class MigrateExistingResearchers extends Command
{
    private const MIGRATION_NOTE = 'Membresía migrada desde el flujo anterior de RIMIS.';

    protected $signature = 'rimis:migrate-existing-researchers';

    protected $description = 'Crea postulaciones aprobadas para investigadores existentes sin alterar sus roles';

    public function handle(): int
    {
        $researchers = User::role('INVESTIGADOR')->with('researcherApplication')->get();
        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($researchers as $researcher) {
            if ($researcher->researcherApplication) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($researcher) {
                    $now = now();
                    $application = $researcher->researcherApplication()->create([
                        'status' => ResearcherApplication::STATUS_APPROVED,
                        'profile_snapshot' => $researcher->researcherProfile?->toArray(),
                        'submitted_at' => $now,
                        'reviewed_at' => $now,
                        'review_notes' => self::MIGRATION_NOTE,
                    ]);

                    $application->history()->create([
                        'previous_status' => null,
                        'new_status' => ResearcherApplication::STATUS_APPROVED,
                        'comments' => self::MIGRATION_NOTE,
                        'changed_by' => null,
                    ]);
                });
                $migrated++;
            } catch (Throwable $exception) {
                $errors++;
                $this->error("{$researcher->email}: {$exception->getMessage()}");
            }
        }

        $this->table(['Resultado', 'Cantidad'], [
            ['Investigadores encontrados', $researchers->count()],
            ['Migrados', $migrated],
            ['Omitidos', $skipped],
            ['Errores', $errors],
        ]);

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }
}
