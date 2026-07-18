<?php

namespace App\Providers;

use App\Models\ResearcherApplication;
use App\Policies\ResearcherApplicationPolicy;
use App\Models\Event;
use App\Models\Bulletin;
use App\Models\CallForProposal;
use App\Policies\EventPolicy;
use App\Policies\BulletinPolicy;
use App\Policies\CallForProposalPolicy;
use App\Models\ResearchPublication;
use App\Policies\ResearchPublicationPolicy;
use App\Policies\ResearcherProfilePolicy;
use App\Models\ResearcherProfile;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ResearcherApplication::class => ResearcherApplicationPolicy::class,
        Event::class => EventPolicy::class,
        Bulletin::class => BulletinPolicy::class,
        CallForProposal::class => CallForProposalPolicy::class,
        ResearchPublication::class => ResearchPublicationPolicy::class,
        ResearcherProfile::class => ResearcherProfilePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
