<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\GrantCall;
use App\Models\Proposal;
use App\Models\ReviewerAppointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletesGrantSop;
use Tests\TestCase;

class GrantDeepAuditTest extends TestCase
{
    use CompletesGrantSop, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    private function actor(string $role, array $extra = []): User
    {
        return User::factory()->create(array_merge(['role' => $role, 'faculty' => 'Teknik', 'study_program' => 'Informatika', 'sso_roles' => [], 'sso_permissions' => []], $extra));
    }

    private function proposal(User $owner, string $stage = 'review'): Proposal
    {
        return Proposal::create(['title' => 'AUDIT-HIBAH-PRIVATE', 'type' => 'Penelitian Hibah', 'scheme' => 'Internal', 'year' => 2026, 'user_id' => $owner->user_id, 'status' => 'review', 'grant_stage' => $stage]);
    }

    public function test_kaprodi_cannot_read_another_faculty_with_same_program_name(): void
    {
        $owner = $this->actor('dosen', ['faculty' => 'Fakultas Lain']);
        $p = $this->proposal($owner);
        $head = $this->actor('kaprodi');
        $this->actingAs($head)->get(route('grants.show', $p))->assertForbidden();
    }

    public function test_dean_reviewer_combined_roles_retain_faculty_access(): void
    {
        $p = $this->proposal($this->actor('dosen'));
        $dean = $this->actor('dekan', ['sso_roles' => ['reviewer']]);
        $this->actingAs($dean)->get(route('grants.show', $p))->assertOk();
    }

    public function test_grant_assignment_appears_in_review_work_queue(): void
    {
        $p = $this->proposal($this->actor('dosen'));
        $reviewer = $this->actor('reviewer');
        $admin = $this->actor('lppm_admin');
        $p->grantRecords()->create(['actor_id' => $admin->user_id, 'kind' => 'assignment', 'data' => ['reviewer_id' => $reviewer->user_id, 'review_kind' => 'proposal', 'event_date' => today()->toDateString()]]);
        $this->actingAs($reviewer)->get(route('reviews.index'))->assertOk()->assertSee($p->title);
    }

    public function test_admin_cannot_replace_proposal_content_during_review(): void
    {
        $p = $this->proposal($this->actor('dosen'));
        $admin = $this->actor('lppm_admin');
        $this->actingAs($admin)->post(route('grants.documents', $p), ['document_type' => 'proposal', 'file' => UploadedFile::fake()->create('changed.pdf', 10, 'application/pdf')])->assertSessionHasErrors('document_type');
    }

    public function test_only_current_round_assignments_are_rendered_as_assessment_forms(): void
    {
        $p = $this->proposal($this->actor('dosen'));
        $reviewer = $this->actor('reviewer');
        $admin = $this->actor('lppm_admin');
        $old = $p->grantRecords()->create(['actor_id' => $admin->user_id, 'kind' => 'assignment', 'data' => ['reviewer_id' => $reviewer->user_id, 'review_kind' => 'proposal', 'event_date' => today()->toDateString()]]);
        $p->grantRecords()->create(['actor_id' => $p->user_id, 'kind' => 'resubmit', 'to_stage' => 'review']);
        $this->actingAs($reviewer)->get(route('grants.show', $p))->assertOk()->assertDontSee('name="assignment_id" value="'.$old->id.'"', false);
    }

    public function test_existing_archive_scan_must_also_be_pdf(): void
    {
        $owner = $this->actor('dosen');
        $admin = $this->actor('lppm_admin');
        $head = $this->actor('kepala_lppm');
        $p = $this->proposal($owner, 'archive');
        $this->completeSopStage('proposal', $p, ['owner' => $owner, 'admin' => $admin, 'head' => $head]);
        $this->actingAs($admin)->post(route('grants.documents', $p), ['document_type' => 'archive_scan', 'file' => new UploadedFile(public_path('images/logos/logo_lppm.jpg'), 'bad-scan.jpg', 'image/jpeg', null, true)])->assertSessionHasErrors('file');
        // Legacy files must be checked too, even if they predate upload validation.
        Storage::disk('local')->put('legacy-scan.jpg', file_get_contents(public_path('images/logos/logo_lppm.jpg')));
        $doc = Document::create(['proposal_id' => $p->proposal_id, 'uploaded_by' => $admin->user_id, 'file_name' => 'legacy-scan.jpg', 'file_path' => 'legacy-scan.jpg', 'document_type' => 'archive_scan']);
        $data = $this->sopPayload(config('grant_sop')['40.3'], $p);
        unset($data['files']);
        $data['existing_documents']['archive_scan'] = $doc->document_id;
        $this->actingAs($admin)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '40.3']), $data)->assertSessionHasErrors('file');
    }

    public function test_external_appointment_without_decree_file_is_not_ready(): void
    {
        $a = ReviewerAppointment::create(['user_id' => $this->actor('reviewer')->user_id, 'origin' => 'external', 'expertise' => 'Riset', 'institution' => 'Kampus', 'portfolio_path' => 'missing.pdf', 'status' => 'appointed', 'valid_until' => today()->addYear()]);
        // Walk the real external appointment flow before removing its decree.
        $a->update(['status' => 'registered', 'valid_until' => null]);
        $admin = $this->actor('lppm_admin');
        $rector = $this->actor('rektor');
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'rektor' => $rector]);
        $this->actingAs($admin)->post(route('reviewer-appointments.advance', $a), ['note' => 'Rekomendasi instansi diperiksa'])->assertSessionHasNoErrors()->assertRedirect();
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'rektor' => $rector]);
        $this->actingAs($rector)->post(route('reviewer-appointments.advance', $a), ['note' => 'SK disahkan'])->assertSessionHasNoErrors()->assertRedirect();
        $a = $a->fresh();
        Storage::disk('local')->put($a->portfolio_path, '%PDF-1.4 portfolio');
        $this->assertTrue($a->readyForAssignment());
        Storage::disk('local')->delete($a->decree_path);
        $this->assertFalse($a->readyForAssignment());
    }

    public function test_grant_hub_exposes_current_proposals_for_followup(): void
    {
        $owner = $this->actor('dosen');
        $p = $this->proposal($owner);
        $this->actingAs($owner)->get(route('grants.index'))->assertOk()->assertSee($p->title);
    }

    public function test_same_faculty_kaprodi_can_read_and_dual_dean_can_read_other_program(): void
    {
        $p = $this->proposal($this->actor('dosen'));
        $this->actingAs($this->actor('kaprodi'))->get(route('grants.show', $p))->assertOk();
        $dean = $this->actor('kaprodi', ['sso_roles' => ['dekan'], 'study_program' => 'Program Lain']);
        $this->actingAs($dean)->get(route('grants.show', $p))->assertOk();
    }

    public function test_review_queue_excludes_old_rounds_and_other_reviewers_tasks(): void
    {
        $owner = $this->actor('dosen');
        $p = $this->proposal($owner);
        $reviewer = $this->actor('reviewer');
        $admin = $this->actor('lppm_admin');
        $assignment = ['actor_id' => $admin->user_id, 'kind' => 'assignment', 'data' => ['reviewer_id' => $reviewer->user_id, 'review_kind' => 'proposal', 'event_date' => today()->toDateString()]];
        $p->grantRecords()->create($assignment);
        $p->grantRecords()->create(['actor_id' => $owner->user_id, 'kind' => 'resubmit', 'to_stage' => 'review']);
        $this->actingAs($reviewer)->get(route('reviews.index'))->assertOk()->assertDontSee($p->title);
        $p->grantRecords()->create($assignment);
        $this->actingAs($reviewer)->get(route('reviews.index'))->assertOk()->assertSee($p->title);
        $this->actingAs($this->actor('reviewer'))->get(route('reviews.index'))->assertOk()->assertDontSee($p->title);
    }

    public function test_regular_sop_call_durations_are_accepted(): void
    {
        $admin = $this->actor('lppm_admin');
        foreach (['grant' => 29, 'reviewer' => 13] as $purpose => $days) {
            $this->actingAs($admin)->post(route('grants.calls.store'), ['purpose' => $purpose, 'title' => 'Periode '.$purpose, 'guidelines' => 'Panduan resmi', 'opens_at' => today()->toDateString(), 'closes_at' => today()->addDays($days)->toDateString()])->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertSame(2, GrantCall::count());
    }

    public function test_sso_permissions_remain_authoritative_without_silent_role_escalation(): void
    {
        $admin = $this->actor('lppm_admin', ['sso_permissions' => ['lppm.proposal.view']]);
        $this->assertFalse($admin->hasLppmPermission('lppm.grant.manage'));
        $this->actingAs($admin)->post(route('grants.calls.store'), [])->assertForbidden();
    }

    public function test_valid_existing_pdf_archive_remains_usable(): void
    {
        $owner = $this->actor('dosen');
        $admin = $this->actor('lppm_admin');
        $p = $this->proposal($owner, 'archive');
        $this->completeSopStage('proposal', $p, ['owner' => $owner, 'admin' => $admin, 'head' => $this->actor('kepala_lppm')]);
        Storage::disk('local')->put('indexed.pdf', "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\n%%EOF");
        $doc = Document::create(['proposal_id' => $p->proposal_id, 'uploaded_by' => $admin->user_id, 'file_name' => 'indexed.pdf', 'file_path' => 'indexed.pdf', 'document_type' => 'archive_scan']);
        $data = $this->sopPayload(config('grant_sop')['40.3'], $p);
        unset($data['files']);
        $data['existing_documents']['archive_scan'] = $doc->document_id;
        $this->actingAs($admin)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '40.3']), $data)->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_candidate_is_told_when_sso_scoring_permission_is_missing(): void
    {
        $candidate = $this->actor('dosen');
        ReviewerAppointment::create(['user_id' => $candidate->user_id, 'origin' => 'external', 'expertise' => 'Riset', 'institution' => 'Kampus', 'portfolio_path' => 'portfolio.pdf', 'status' => 'appointed', 'valid_until' => today()->addYear()]);
        $this->actingAs($this->actor('lppm_admin'))->get(route('reviewer-appointments.index'))->assertOk()->assertSee('Hak penilaian belum aktif');
        $this->assertFalse($candidate->fresh()->hasLppmPermission('lppm.review.score'));
    }
}
