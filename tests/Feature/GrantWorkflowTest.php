<?php

namespace Tests\Feature;

use App\Models\ActivityNotification;
use App\Models\Document;
use App\Models\GrantCall;
use App\Models\Proposal;
use App\Models\User;
use App\Services\GrantWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CompletesGrantSop;
use Tests\TestCase;

class GrantWorkflowTest extends TestCase
{
    use CompletesGrantSop;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'faculty' => 'Teknik', 'study_program' => 'Informatika', 'sso_roles' => [], 'sso_permissions' => []]);
    }

    private function proposal(User $owner): Proposal
    {
        $call = GrantCall::create(['title' => 'Hibah 2026', 'guidelines' => 'Panduan', 'opens_at' => today()->subDay(), 'closes_at' => today()->addDays(30), 'published' => true]);

        return Proposal::create(['title' => 'Riset', 'type' => 'Penelitian Hibah', 'scheme' => 'Internal', 'year' => 2026, 'user_id' => $owner->user_id, 'status' => 'draft', 'grant_stage' => 'draft', 'grant_call_id' => $call->id]);
    }

    private function doc(Proposal $p, string $kind, User $u): void
    {
        Document::create(['proposal_id' => $p->proposal_id, 'file_name' => $kind.'.pdf', 'file_path' => 'private/'.$kind.'.pdf', 'document_type' => $kind, 'uploaded_by' => $u->user_id]);
    }

    private function assessmentDocument(Proposal $p, User $reviewer): string
    {
        $path = 'assessment/'.$p->proposal_id.'/'.$reviewer->user_id.'.pdf';
        Storage::disk('local')->put($path, 'Completed prior-round rubric fixture');

        return Document::create(['proposal_id' => $p->proposal_id, 'uploaded_by' => $reviewer->user_id, 'file_name' => 'borang.pdf', 'file_path' => $path, 'document_type' => 'assessment_form'])->document_id;
    }

    public static function grantTypes(): array
    {
        return ['penelitian' => ['Penelitian Hibah'], 'pengabdian' => ['Pengabdian Hibah']];
    }

    #[DataProvider('grantTypes')]
    public function test_full_grant_workflow_requires_evidence_and_records_exact_disbursement(string $type): void
    {
        $owner = $this->user('dosen');
        $admin = $this->user('lppm_admin');
        $dean = $this->user('dekan');
        $rector = $this->user('rektor');
        $finance = $this->user('keuangan');
        $head = $this->user('kepala_lppm');
        $reviewer = $this->user('reviewer');
        $this->appointReviewer($reviewer, $admin, $dean, $rector);
        $p = $this->proposal($owner);
        $p->update(['type' => $type]);
        $actors = ['owner' => $owner, 'admin' => $admin, 'dekan' => $dean, 'faculty' => $dean, 'rektor' => $rector, 'finance' => $finance, 'head' => $head];
        foreach (GrantWorkflow::RULES as $stage => $rule) {
            $p = $p->fresh();
            $this->assertSame($stage, $p->grant_stage);
            if ($stage === 'execution') {
                $this->actingAs($owner)->post(route('grants.records', $p), ['kind' => 'logbook', 'event_date' => today()->toDateString(), 'note' => 'Kegiatan lapangan dan hasil'])->assertSessionHasNoErrors()->assertRedirect();
            }
            if (in_array($stage, ['review', 'monev', 'seminar'])) {
                if ($stage === 'monev') {
                    $this->completeOneSop('proposal', $p, '11.1', $admin);
                }
                if ($stage === 'seminar') {
                    $this->completeOneSop('proposal', $p, '9.1', $admin);
                }
                $kind = ['review' => 'proposal', 'monev' => 'monev', 'seminar' => 'seminar'][$stage];
                $this->actingAs($admin)->post(route('grants.records', $p), ['kind' => 'assignment', 'review_kind' => $kind, 'reviewer_id' => $reviewer->user_id, 'event_date' => today()->toDateString(), 'note' => 'Surat tugas dan kriteria resmi'])->assertSessionHasNoErrors()->assertRedirect();
                if ($stage === 'monev') {
                    $this->completeOneSop('proposal', $p, '11.2', $admin);
                }
                if ($stage === 'seminar') {
                    $this->completeOneSop('proposal', $p, '9.2', $owner);
                }
                $assignment = $p->grantRecords()->where('kind', 'assignment')->latest('id')->firstOrFail();
                $this->actingAs($reviewer)->post(route('grants.records', $p), ['kind' => 'assessment', 'assignment_id' => $assignment->id, 'score' => 90, 'note' => 'Hasil penilaian', 'criteria_reference' => 'Borang resmi', 'strengths' => 'Capaian sesuai', 'improvements' => 'Perbaikan terdokumentasi', 'recommendation' => 'approve', 'presentation_confirmed' => 1, 'assessment_form' => UploadedFile::fake()->create('penilaian.pdf', 10, 'application/pdf')])->assertSessionHasNoErrors()->assertRedirect();
            }
            $this->completeSopStage('proposal', $p, $actors);
            $actor = $actors[$rule[1]];
            $this->actingAs($actor)->get(route('grants.show', $p))->assertOk();
            $this->actingAs($actor)->get(route('grant-sop.show', ['proposal', $p->proposal_id]))->assertOk();
            $this->actingAs($actor)->post(route('grants.advance', $p), ['decision' => 'advance', 'grant_amount' => 10000001, 'checklist_confirmed' => 1])->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertSame('completed', $p->fresh()->grant_stage);
        $payments = $p->grantRecords()->whereIn('from_stage', ['payment70', 'payment30'])->where('kind', 'advance')->get();
        $this->assertSame(10000001, $payments->sum(fn ($r) => $r->data['amount']));
        $this->assertCount(16, $p->grantRecords()->where('kind', 'advance')->get());
        $this->assertSame(5, $p->reports()->count());
        $this->assertSame(5, $p->reports()->whereNotNull('accepted_at')->count());
        $this->assertNotEmpty($p->fresh()->registration_number);
        $this->actingAs($owner)->get(route('reports.index'))->assertOk()->assertSee('Laporan akhir');
    }

    public function test_owner_cannot_approve_own_proposal_or_skip_document_requirements(): void
    {
        $owner = $this->user('dosen');
        $p = $this->proposal($owner);
        $this->actingAs($owner)->post(route('grants.advance', $p), ['decision' => 'advance'])->assertRedirect();
        $this->actingAs($owner)->post(route('grants.advance', $p), ['decision' => 'advance'])->assertForbidden();
        $dean = $this->user('dekan');
        $this->actingAs($dean)->post(route('grants.advance', $p), ['decision' => 'advance'])->assertSessionHasErrors('sop');
        $this->assertSame('endorsement', $p->fresh()->grant_stage);
    }

    public function test_private_documents_and_pages_are_not_visible_to_other_researchers(): void
    {
        Storage::fake('local');
        $owner = $this->user('dosen');
        $other = $this->user('dosen');
        $p = $this->proposal($owner);
        $this->actingAs($owner)->post(route('grants.documents', $p), ['document_type' => 'proposal', 'file' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf')])->assertRedirect();
        $d = $p->documents()->firstOrFail();
        $this->actingAs($other)->get(route('documents.download', $d))->assertForbidden();
        $this->actingAs($other)->get(route('grants.show', $p))->assertForbidden();
        $this->actingAs($owner)->get(route('documents.download', $d))->assertOk();
        $this->actingAs($owner)->get(route('grants.show', $p))->assertOk();
    }

    public function test_revision_returns_to_requesting_stage_and_preserves_history(): void
    {
        $owner = $this->user('dosen');
        $p = $this->proposal($owner);
        $p->update(['grant_stage' => 'validation', 'status' => 'submitted']);
        $admin = $this->user('lppm_admin');
        $this->actingAs($admin)->post(route('grants.advance', $p), ['decision' => 'revision', 'note' => 'Lengkapi metode'])->assertRedirect();
        $this->actingAs($owner)->put(route('proposals.update', $p), ['title' => 'Metode diperbaiki', 'summary' => 'Versi dua'])->assertRedirect();
        $this->actingAs($owner)->post(route('grants.advance', $p), ['decision' => 'resubmit'])->assertRedirect();
        $this->assertSame('validation', $p->fresh()->grant_stage);
        $this->assertSame(3, $p->grantRecords()->count());
    }

    public function test_api_does_not_allow_impersonation_or_expose_other_proposals(): void
    {
        $owner = $this->user('dosen');
        $other = $this->user('dosen');
        $p = $this->proposal($owner);
        $this->actingAs($other)->getJson('/api/proposals')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($other)->postJson('/api/proposals', ['title' => 'Palsu', 'type' => 'Penelitian Hibah', 'scheme' => 'Internal', 'year' => 2026, 'user_id' => $owner->user_id, 'status' => 'approved'])->assertUnprocessable()->assertJsonValidationErrors(['user_id', 'status']);
    }

    public function test_closed_calls_cannot_be_submitted_and_legacy_endpoint_cannot_bypass_workflow(): void
    {
        $owner = $this->user('dosen');
        $p = $this->proposal($owner);
        $p->grantCall->update(['closes_at' => today()->subDay()]);
        $this->actingAs($owner)->post(route('grants.advance', $p), ['decision' => 'advance'])->assertSessionHasErrors('workflow');
        $this->actingAs($owner)->post(route('proposals.advance', $p), ['decision' => 'approve'])->assertUnprocessable();
    }

    public function test_sso_callback_rejects_missing_or_mismatched_state(): void
    {
        $this->get('/auth/sso/callback?code=fake')->assertForbidden();
        $this->withSession(['sso_state' => 'expected'])->get('/auth/sso/callback?code=fake&state=wrong')->assertForbidden();
    }

    public function test_review_before_revision_cannot_satisfy_the_new_round(): void
    {
        $owner = $this->user('dosen');
        $admin = $this->user('lppm_admin');
        $reviewer = $this->user('reviewer');
        $p = $this->proposal($owner);
        $p->update(['grant_stage' => 'review', 'status' => 'review']);
        $a = $p->grantRecords()->create(['actor_id' => $admin->user_id, 'kind' => 'assignment', 'data' => ['reviewer_id' => $reviewer->user_id, 'review_kind' => 'proposal']]);
        $p->grantRecords()->create(['actor_id' => $reviewer->user_id, 'kind' => 'assessment', 'data' => ['assignment_id' => $a->id, 'score' => 90, 'criteria_reference' => 'Borang resmi', 'strengths' => 'Baik', 'improvements' => 'Cukup', 'recommendation' => 'approve', 'assessment_document_id' => $this->assessmentDocument($p, $reviewer), 'presentation_confirmed' => true]]);
        $this->actingAs($admin)->post(route('grants.advance', $p), ['decision' => 'revision', 'note' => 'Perbaiki metode'])->assertRedirect();
        $this->actingAs($owner)->post(route('grants.advance', $p), ['decision' => 'resubmit'])->assertSessionHasErrors('workflow');
        $this->actingAs($owner)->post(route('grants.records', $p), ['kind' => 'followup', 'note' => 'Metode diperbaiki'])->assertRedirect();
        $this->actingAs($owner)->post(route('grants.advance', $p), ['decision' => 'resubmit'])->assertSessionHasNoErrors()->assertRedirect();
        $this->actingAs($admin)->post(route('grants.advance', $p), ['decision' => 'advance'])->assertSessionHasErrors('workflow');
        $this->actingAs($reviewer)->post(route('grants.records', $p), ['kind' => 'assessment', 'assignment_id' => $a->id, 'score' => 90, 'note' => 'Penilaian lama', 'criteria_reference' => 'Borang', 'strengths' => 'Baik', 'improvements' => 'Revisi', 'recommendation' => 'approve', 'assessment_form' => UploadedFile::fake()->create('borang.pdf', 10, 'application/pdf')])->assertUnprocessable();
        $this->assertSame('review', $p->fresh()->grant_stage);
    }

    public function test_legacy_enrollment_preserves_files_and_logs_previous_status(): void
    {
        $owner = $this->user('dosen');
        $p = $this->proposal($owner);
        $call = $p->grant_call_id;
        $p->update(['grant_stage' => null, 'grant_call_id' => null, 'status' => 'approved']);
        $this->doc($p, 'proposal', $owner);
        $this->actingAs($owner)->post(route('grants.enroll', $p), ['grant_call_id' => $call])->assertRedirect(route('grants.show', $p));
        $this->assertSame('draft', $p->fresh()->grant_stage);
        $this->assertSame(1, $p->documents()->count());
        $this->assertSame('approved', $p->grantRecords()->first()->data['previous_status']);
    }

    public function test_deadline_reminder_is_idempotent_within_the_day(): void
    {
        $owner = $this->user('dosen');
        $p = $this->proposal($owner);
        $p->update(['stage_due_at' => now()->addDay()]);
        $this->artisan('grants:remind')->assertSuccessful();
        $this->artisan('grants:remind')->assertSuccessful();
        $this->assertSame(1, ActivityNotification::where('channel', 'hibah-reminder')->count());
    }
}
