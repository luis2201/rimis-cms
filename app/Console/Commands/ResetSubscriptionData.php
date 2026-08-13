<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB,Storage};
class ResetSubscriptionData extends Command
{
    protected $signature='rimis:reset-subscriptions {--force}';
    protected $description='Elimina membresías y datos asociados conservando administradores, webmasters y contenido editorial independiente.';
    public function handle():int
    {
        if(!$this->option('force')&&!$this->confirm('¿Eliminar todas las suscripciones y usuarios no administrativos?'))return self::FAILURE;
        $keepers=User::role(['ADMINISTRADOR','WEBMASTER'])->pluck('id');$remove=User::whereNotIn('id',$keepers)->pluck('id');
        $paths=DB::table('researcher_profiles')->whereIn('user_id',$remove)->whereNotNull('cv_path')->pluck('cv_path');
        DB::transaction(function()use($remove){
            DB::table('subscriptions')->delete();
            if($remove->isEmpty())return;
            $publicationIds=DB::table('research_publications')->whereIn('user_id',$remove)->pluck('id');
            if($publicationIds->isNotEmpty())DB::table('research_publications')->whereIn('id',$publicationIds)->delete();
            foreach(['events','bulletins','calls'] as $table)DB::table($table)->whereIn('user_id',$remove)->delete();
            DB::table('researcher_applications')->whereIn('user_id',$remove)->delete();
            DB::table('researcher_profiles')->whereIn('user_id',$remove)->delete();
            User::whereIn('id',$remove)->delete();
        });
        foreach($paths as $path)Storage::disk('local')->delete($path);
        $this->info("Reinicio completado. Usuarios conservados: {$keepers->count()}; eliminados: {$remove->count()}; suscripciones: 0.");return self::SUCCESS;
    }
}
