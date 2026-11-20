<?php

namespace Tests\Audit;

use App\Models\ActivityNotification;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrantEdgeAuditTest extends TestCase
{
    use RefreshDatabase;

    private function actor($role, $permissions = [])
    {
        return User::factory()->create(['role' => $role, 'sso_roles' => [], 'sso_permissions' => $permissions]);
    }

    private function proposal($owner)
    {
        return Proposal::create(['title' => 'Audit edge hibah', 'type' => 'Penelitian Hibah', 'scheme' => 'Internal', 'year' => 2026, 'user_id' => $owner->user_id, 'status' => 'review', 'grant_stage' => 'review']);
    }

    public function test_real_sanctum_token_can_be_created_for_uuid_user(): void
    {
        $u = $this->actor('dosen');
        $token = $u->createToken('audit-local')->plainTextToken;
        $this->assertNotEmpty($token);
    }

    public function test_sso_scoring_permission_can_access_assigned_proposal_without_reviewer_role(): void
    {
        $this->withoutVite();
        $owner = $this->actor('dosen');
        $p = $this->proposal($owner);
        $r = $this->actor('dosen', ['lppm.proposal.view', 'lppm.review.view', 'lppm.review.score']);
        $p->grantRecords()->create(['actor_id' => $owner->user_id, 'kind' => 'assignment', 'data' => ['reviewer_id' => $r->user_id, 'review_kind' => 'proposal']]);
        $this->actingAs($r)->get(route('grants.show', $p))->assertOk();
    }

    public function test_obsolete_round_does_not_send_assessment_deadline_reminder(): void
    {
        $owner = $this->actor('dosen');
        $p = $this->proposal($owner);
        $r = $this->actor('reviewer');
        $p->grantRecords()->create(['actor_id' => $owner->user_id, 'kind' => 'assignment', 'data' => ['reviewer_id' => $r->user_id, 'review_kind' => 'proposal', 'assessment_due_at' => now()->subDay()->toIso8601String()]]);
        $p->grantRecords()->create(['actor_id' => $owner->user_id, 'kind' => 'resubmit', 'to_stage' => 'review']);
        $this->artisan('grants:remind')->assertExitCode(0);
        $this->assertSame(0, ActivityNotification::where('user_id', $r->user_id)->where('message', 'like', 'Tenggat penilaian tugas #%')->count());
    }
}
