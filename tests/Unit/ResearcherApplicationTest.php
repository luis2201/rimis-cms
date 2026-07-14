<?php

namespace Tests\Unit;

use App\Models\ResearcherApplication;
use App\Models\ResearcherApplicationHistory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearcherApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_helpers_and_editability(): void
    {
        $application = new ResearcherApplication(['status' => ResearcherApplication::STATUS_DRAFT]);
        $this->assertTrue($application->isDraft());
        $this->assertTrue($application->isEditableByApplicant());

        $application->status = ResearcherApplication::STATUS_OBSERVED;
        $this->assertTrue($application->isObserved());
        $this->assertTrue($application->isEditableByApplicant());

        foreach ([
            ResearcherApplication::STATUS_SUBMITTED => 'isSubmitted',
            ResearcherApplication::STATUS_UNDER_REVIEW => 'isUnderReview',
            ResearcherApplication::STATUS_APPROVED => 'isApproved',
            ResearcherApplication::STATUS_REJECTED => 'isRejected',
            ResearcherApplication::STATUS_WITHDRAWN => 'isWithdrawn',
        ] as $status => $method) {
            $application->status = $status;
            $this->assertTrue($application->{$method}());
            $this->assertFalse($application->isEditableByApplicant());
        }

        $this->assertSame('En revisión', ResearcherApplication::statusLabels()[ResearcherApplication::STATUS_UNDER_REVIEW]);
    }

    public function test_user_reviewer_and_history_relationships(): void
    {
        $applicant = User::factory()->create();
        $reviewer = User::factory()->create();
        $application = ResearcherApplication::create([
            'user_id' => $applicant->id,
            'reviewed_by' => $reviewer->id,
            'status' => ResearcherApplication::STATUS_UNDER_REVIEW,
        ]);
        $history = $application->history()->create([
            'new_status' => ResearcherApplication::STATUS_UNDER_REVIEW,
            'changed_by' => $reviewer->id,
        ]);

        $this->assertTrue($applicant->researcherApplication->is($application));
        $this->assertTrue($reviewer->reviewedResearcherApplications->contains($application));
        $this->assertTrue($reviewer->researcherApplicationHistoryChanges->contains($history));
        $this->assertTrue($application->user->is($applicant));
        $this->assertTrue($application->reviewer->is($reviewer));
        $this->assertTrue($history->application->is($application));
        $this->assertTrue($history->changedBy->is($reviewer));
    }

    public function test_only_one_application_is_allowed_per_user(): void
    {
        $user = User::factory()->create();
        ResearcherApplication::create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);
        ResearcherApplication::create(['user_id' => $user->id]);
    }

    public function test_deleting_user_cascades_application_and_history(): void
    {
        $user = User::factory()->create();
        $application = ResearcherApplication::create(['user_id' => $user->id]);
        $history = ResearcherApplicationHistory::create([
            'researcher_application_id' => $application->id,
            'new_status' => ResearcherApplication::STATUS_DRAFT,
        ]);

        $user->delete();

        $this->assertDatabaseMissing('researcher_applications', ['id' => $application->id]);
        $this->assertDatabaseMissing('researcher_application_history', ['id' => $history->id]);
    }
}
