<?php

namespace Tests\Feature;

use App\Models\GrantCall;
use App\Models\Proposal;
use App\Models\ReviewerAppointment;
use App\Models\SopAction;
use App\Models\User;
use App\Services\GrantSop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletesGrantSop;
use Tests\TestCase;

class GrantSopAuditTest extends TestCase
{
    use CompletesGrantSop,RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    private function actor($role)
    {
        return User::factory()->create(['role' => $role, 'faculty' => 'Teknik', 'study_program' => 'Informatika', 'sso_roles' => [], 'sso_permissions' => []]);
    }

    private function proposal($owner, $stage = 'archive')
    {
        return Proposal::create(['title' => 'Riset Hibah', 'type' => 'Penelitian Hibah', 'scheme' => 'Internal', 'year' => 2026, 'user_id' => $owner->user_id, 'status' => 'approved', 'grant_stage' => $stage, 'grant_amount' => 10000001]);
    }

    public function test_missing_checklist_and_evidence_cannot_complete_sop(): void
    {
        $owner = $this->actor('dosen');
        $dean = $this->actor('dekan');
        $p = $this->proposal($owner, 'endorsement');
        $this->actingAs($dean)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '5.2']), ['fields' => ['reference' => 'SK', 'signed_at' => today()->toDateString()]])->assertSessionHasErrors(['checks.faculty_matches', 'checks.proposal_checked']);
        $this->assertSame(0, SopAction::count());
        $data = $this->sopPayload(config('grant_sop')['5.2'], $p);
        unset($data['files']);
        $this->actingAs($dean)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '5.2']), $data)->assertSessionHasErrors('file');
        $this->assertSame(0, SopAction::count());
    }

    public function test_sop_files_and_actions_are_private_and_owner_cannot_endorse(): void
    {
        $owner = $this->actor('dosen');
        $other = $this->actor('dosen');
        $dean = $this->actor('dekan');
        $p = $this->proposal($owner, 'endorsement');
        $data = $this->sopPayload(config('grant_sop')['5.2'], $p);
        $this->actingAs($owner)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '5.2']), $data)->assertForbidden();
        $this->actingAs($dean)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '5.2']), $data)->assertSessionHasNoErrors()->assertRedirect();
        $a = SopAction::firstOrFail();
        $this->actingAs($other)->get(route('grant-sop.show', ['proposal', $p->proposal_id]))->assertForbidden();
        $this->actingAs($other)->get(route('grant-sop.download', [$a, 'endorsement']))->assertForbidden();
        $this->actingAs($owner)->get(route('grant-sop.download', [$a, 'endorsement']))->assertOk();
        $this->actingAs($owner)->get(route('grant-sop.receipt', $a))->assertOk()->assertSee('Bukti Pencatatan');
        $this->actingAs($other)->get(route('grant-sop.receipt', $a))->assertForbidden();
        $this->actingAs($owner)->get(route('grant-sop.show', ['proposal', $p->proposal_id]))->assertOk();
    }

    public function test_internal_recommendation_review_belongs_to_dean_or_vice_rector(): void
    {
        $candidate = $this->actor('reviewer');
        $admin = $this->actor('lppm_admin');
        $dean = $this->actor('dekan');
        $a = ReviewerAppointment::create(['user_id' => $candidate->user_id, 'origin' => 'internal', 'expertise' => 'Informatika', 'institution' => 'UMB', 'portfolio_path' => 'portfolio.pdf']);
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'dean_wr' => $dean]);
        $a->update(['status' => 'recommended']);
        $data = $this->sopPayload(config('grant_sop')['6.3'], $a);
        $this->actingAs($admin)->post(route('grant-sop.store', ['reviewer', $a->id, '6.3']), $data)->assertForbidden();
        $this->actingAs($dean)->post(route('grant-sop.store', ['reviewer', $a->id, '6.3']), $data)->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_external_request_is_required_before_institution_recommendation(): void
    {
        $candidate = $this->actor('reviewer');
        $admin = $this->actor('lppm_admin');
        $rector = $this->actor('rektor');
        $a = ReviewerAppointment::create(['user_id' => $candidate->user_id, 'origin' => 'external', 'expertise' => 'Informatika', 'institution' => 'Instansi luar', 'portfolio_path' => 'portfolio.pdf']);
        $this->actingAs($admin)->post(route('grant-sop.store', ['reviewer', $a->id, '7.3']), $this->sopPayload(config('grant_sop')['7.3'], $a))->assertSessionHasErrors('sop');
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'rektor' => $rector]);
        $this->actingAs($admin)->post(route('reviewer-appointments.advance', $a), ['note' => 'Rekomendasi resmi diterima'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('selected', $a->fresh()->status);
        $this->assertNotNull(app(GrantSop::class)->action('reviewer', $a, '7.2'));
    }

    public function test_payment_amount_must_match_the_exact_tranche(): void
    {
        $owner = $this->actor('dosen');
        $finance = $this->actor('keuangan');
        $p = $this->proposal($owner, 'payment70');
        $data = $this->sopPayload(config('grant_sop')['18.3'], $p);
        $data['fields']['amount'] = 10000001;
        $this->actingAs($finance)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '18.3']), $data)->assertSessionHasErrors('fields.amount');
        $this->assertSame(0, $p->documents()->count());
    }

    public function test_retention_records_do_not_delete_original_archive_files(): void
    {
        $owner = $this->actor('dosen');
        $admin = $this->actor('lppm_admin');
        $head = $this->actor('kepala_lppm');
        $p = $this->proposal($owner);
        $this->completeSopStage('proposal', $p, ['owner' => $owner, 'admin' => $admin, 'head' => $head]);
        $p->update(['grant_stage' => 'completed', 'status' => 'completed']);
        $retention = $this->sopPayload(config('grant_sop')['40.6'], $p);
        $retention['fields']['action'] = 'physical_disposal';
        $this->actingAs($head)->post(route('grant-sop.store', ['proposal', $p->proposal_id, '40.6']), $retention)->assertSessionHasNoErrors()->assertRedirect();
        foreach ($p->documents as $document) {
            Storage::disk('local')->assertExists($document->file_path);
        }
        $this->assertNotNull(app(GrantSop::class)->action('proposal', $p, '40.6'));
    }

    public function test_annual_reporting_sync_and_distribution_are_separate_audited_actions(): void
    {
        $admin = $this->actor('lppm_admin');
        $rector = $this->actor('rektor');
        $call = GrantCall::create(['title' => 'Hibah', 'guidelines' => 'Panduan', 'opens_at' => today(), 'closes_at' => today()->addDays(30), 'published' => true]);
        foreach (app(GrantSop::class)->definitions('call', $call) as $key => $d) {
            if ($d['stage'] !== 'annual') {
                continue;
            }
            $actor = $d['role'] === 'rector_wr' ? $rector : $admin;
            $this->actingAs($actor)->post(route('grant-sop.store', ['call', $call->id, $key]), $this->sopPayload($d, $call))->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertNotNull(app(GrantSop::class)->action('call', $call, '39.4'));
        $this->actingAs($admin)->get(route('grant-sop.show', ['call', $call->id]))->assertOk();
        $this->actingAs($admin)->get(route('grants.calls.export', $call))->assertOk();
    }

    public function test_reviewer_dispatch_requires_real_letter_and_delivery_proof(): void
    {
        $candidate = $this->actor('reviewer');
        $admin = $this->actor('lppm_admin');
        $rector = $this->actor('rektor');
        $a = ReviewerAppointment::create(['user_id' => $candidate->user_id, 'origin' => 'internal', 'expertise' => 'Informatika', 'institution' => 'UMB', 'portfolio_path' => 'portfolio.pdf', 'status' => 'appointed']);
        $this->completeSopStage('reviewer', $a, ['admin' => $admin]);
        $this->actingAs($rector)->post(route('grant-sop.store', ['reviewer', $a->id, '35.5']), $this->sopPayload(config('grant_sop')['35.5'], $a))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertCount(2, app(GrantSop::class)->action('reviewer', $a, '35.5')->files);
    }

    public function test_catalog_has_pdf_provenance_and_no_invented_review_deadline(): void
    {
        foreach (config('grant_sop') as $key => $d) {
            $this->assertNotEmpty($d['page']);
            $this->assertNotEmpty($d['output']);
            $this->assertNotEmpty($d['role']);
        }
        $owner = $this->actor('dosen');
        $p = $this->proposal($owner, 'review');
        $admin = $this->actor('lppm_admin');
        $this->actingAs($admin)->get(route('grant-sop.show', ['proposal', $p->proposal_id]))->assertOk();
        $this->assertFalse(array_key_exists('review', config('grant_sop')));
    }

    public function test_proposal_documents_are_frozen_during_review(): void
    {
        $owner = $this->actor('dosen');
        $p = $this->proposal($owner, 'review');
        $this->actingAs($owner)->post(route('grants.documents', $p), ['document_type' => 'proposal', 'file' => UploadedFile::fake()->create('berubah.pdf', 10, 'application/pdf')])->assertSessionHasErrors('document_type');
        $this->assertSame(0, $p->documents()->count());
    }

    public function test_schedule_variation_requires_official_reason(): void
    {
        $admin = $this->actor('lppm_admin');
        $data = ['purpose' => 'grant', 'title' => 'Hibah', 'guidelines' => 'Panduan', 'opens_at' => today()->toDateString(), 'closes_at' => today()->addDays(5)->toDateString()];
        $this->actingAs($admin)->post(route('grants.calls.store'), $data)->assertSessionHasErrors('schedule_exception');
        $this->actingAs($admin)->post(route('grants.calls.store'), $data + ['schedule_exception' => 'Perubahan kalender berdasarkan surat resmi nomor 01'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertFalse(GrantCall::first()->published);
    }

    public function test_sop_due_dates_use_published_durations_and_dependencies(): void
    {
        $owner = $this->actor('dosen');
        $dean = $this->actor('dekan');
        $p = $this->proposal($owner, 'endorsement');
        $sop = app(GrantSop::class);
        $this->assertTrue($sop->dueAt('proposal', $p, '5.2')->equalTo($p->created_at->copy()->addDays(3)));
        $p->update(['grant_stage' => 'archive']);
        $this->assertNull($sop->dueAt('proposal', $p, '40.5'));
        $call = GrantCall::create(['title' => 'Hibah', 'guidelines' => 'Panduan', 'opens_at' => today(), 'closes_at' => today()->addDays(29), 'published' => true]);
        $this->assertNull($sop->dueAt('call', $call, '38.7'));
    }

    public function test_internal_decree_without_dispatch_cannot_be_used_for_assignment(): void
    {
        $owner = $this->actor('dosen');
        $admin = $this->actor('lppm_admin');
        $reviewer = $this->actor('reviewer');
        $p = $this->proposal($owner, 'review');
        ReviewerAppointment::create(['user_id' => $reviewer->user_id, 'origin' => 'internal', 'institution' => 'UMB', 'expertise' => 'Informatika', 'portfolio_path' => 'portfolio.pdf', 'decree_path' => 'decree.pdf', 'status' => 'appointed', 'published' => true, 'valid_until' => today()->addYear()]);
        $this->actingAs($admin)->post(route('grants.records', $p), ['kind' => 'assignment', 'review_kind' => 'proposal', 'reviewer_id' => $reviewer->user_id, 'event_date' => today()->toDateString(), 'note' => 'Penugasan sebelum administrasi lengkap'])->assertUnprocessable();
        $this->assertSame(0, $p->grantRecords()->where('kind', 'assignment')->count());
    }

    public function test_published_reviewer_files_exclude_portfolios_and_recommendations(): void
    {
        $candidate = $this->actor('reviewer');
        $a = ReviewerAppointment::create(['user_id' => $candidate->user_id, 'origin' => 'external', 'expertise' => 'Informatika', 'institution' => 'Instansi', 'portfolio_path' => 'private.pdf', 'decree_path' => 'public-decree.pdf', 'status' => 'appointed', 'published' => true, 'valid_until' => today()->addYear()]);
        Storage::disk('local')->put('public-decree.pdf', 'Official decree');
        auth()->logout();
        $this->get(route('grants.reviewer-announcement', [$a, 'decree']))->assertOk();
        $this->get(route('grants.reviewer-announcement', [$a, 'portfolio']))->assertNotFound();
        $this->get(route('grants.reviewer-announcement', [$a, 'recommendation']))->assertNotFound();
    }

    public function test_export_respects_faculty_scope_and_escapes_formula_titles(): void
    {
        $dean = $this->actor('dekan');
        $owner = $this->actor('dosen');
        $other = $this->actor('dosen');
        $other->update(['faculty' => 'Fakultas lain']);
        $call = GrantCall::create(['title' => 'Hibah', 'guidelines' => 'Panduan', 'opens_at' => today(), 'closes_at' => today()->addDays(29), 'published' => true]);
        $p = $this->proposal($owner, 'review');
        $p->update(['grant_call_id' => $call->id, 'title' => '=FORMULA()']);
        $hidden = $this->proposal($other, 'review');
        $hidden->update(['grant_call_id' => $call->id, 'title' => 'JANGAN TERLIHAT']);
        $response = $this->actingAs($dean)->get(route('grants.calls.export', $call));
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=FORMULA()", $csv);
        $this->assertStringNotContainsString('JANGAN TERLIHAT', $csv);
    }

    public function test_secretariat_can_record_socialisation_but_cannot_publish(): void
    {
        $admin = $this->actor('lppm_admin');
        $staff = $this->actor('sekretaris_lppm');
        $call = GrantCall::create(['title' => 'Hibah', 'guidelines' => 'Panduan', 'opens_at' => today(), 'closes_at' => today()->addDays(29), 'published' => false]);
        $this->completeOneSop('call', $call, '4.1', $admin);
        $this->actingAs($staff)->get(route('grant-sop.show', ['call', $call->id]))->assertOk();
        $this->completeOneSop('call', $call, '4.2', $staff);
        $this->actingAs($staff)->post(route('grants.calls.publish', $call))->assertForbidden();
    }

    public function test_missing_evidence_invalidates_a_completed_sop_step(): void
    {
        $owner = $this->actor('dosen');
        $dean = $this->actor('dekan');
        $p = $this->proposal($owner, 'endorsement');
        $this->completeOneSop('proposal', $p, '5.2', $dean);
        $a = app(GrantSop::class)->action('proposal', $p, '5.2');
        Storage::disk('local')->delete($a->files['endorsement']['path']);
        $this->assertNull(app(GrantSop::class)->action('proposal', $p, '5.2'));
        $this->actingAs($dean)->post(route('grants.advance', $p), ['decision' => 'advance'])->assertSessionHasErrors('sop');
    }
}
