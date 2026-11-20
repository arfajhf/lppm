<?php

namespace Tests\Feature;

use App\Models\GrantCall;
use App\Models\GrantRecord;
use App\Models\Proposal;
use App\Models\ReviewerAppointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CompletesGrantSop;
use Tests\TestCase;

class ReviewerAppointmentTest extends TestCase
{
    use CompletesGrantSop;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    private function user($role)
    {
        return User::factory()->create(['role' => $role, 'faculty' => 'Teknik', 'sso_roles' => [], 'sso_permissions' => []]);
    }

    private function file()
    {
        return UploadedFile::fake()->create('bukti.pdf', 40, 'application/pdf');
    }

    public function test_reviewer_recruitment_recommendation_selection_and_decree(): void
    {
        $candidate = $this->user('reviewer');
        $dean = $this->user('dekan');
        $admin = $this->user('lppm_admin');
        $rector = $this->user('rektor');
        $call = GrantCall::create(['purpose' => 'reviewer', 'title' => 'Seleksi reviewer', 'guidelines' => 'Kriteria', 'opens_at' => today(), 'closes_at' => today()->addDays(14), 'published' => true]);
        $this->actingAs($candidate)->post(route('reviewer-appointments.store'), ['grant_call_id' => $call->id, 'origin' => 'internal', 'expertise' => 'Informatika', 'institution' => 'UMB', 'portfolio' => $this->file()])->assertRedirect();
        $a = ReviewerAppointment::firstOrFail();
        $this->actingAs($candidate)->post(route('reviewer-appointments.advance', $a), ['note' => 'Setujui sendiri', 'file' => $this->file()])->assertForbidden();
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'dean_wr' => $dean, 'rektor' => $rector]);
        $this->actingAs($dean)->post(route('reviewer-appointments.advance', $a), ['note' => 'Rekomendasi sesuai bidang', 'file' => $this->file()])->assertRedirect();
        $this->assertSame('recommended', $a->fresh()->status);
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'dean_wr' => $dean, 'rektor' => $rector]);
        $this->actingAs($admin)->post(route('reviewer-appointments.advance', $a), ['note' => 'Lulus seleksi keahlian'])->assertRedirect();
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'dean_wr' => $dean, 'rektor' => $rector]);
        $this->actingAs($rector)->post(route('reviewer-appointments.advance', $a), ['note' => 'Penetapan', 'file' => $this->file(), 'decree_number' => 'SK/1/2026', 'valid_until' => today()->addYear()->toDateString()])->assertRedirect();
        $this->assertSame('appointed', $a->fresh()->status);
        $this->actingAs($admin)->get(route('reviewer-appointments.index'))->assertOk();
        $this->actingAs($candidate)->get(route('reviewer-appointments.index'))->assertOk();
        $this->assertCount(4, $a->fresh()->history);
        $this->completeSopStage('reviewer', $a, ['admin' => $admin, 'dean_wr' => $dean, 'rektor' => $rector]);
        $this->actingAs($admin)->post(route('reviewer-appointments.publish', $a))->assertRedirect();
        $this->assertTrue($a->fresh()->published);
        auth()->logout();
        $this->get(route('grants.announcements'))->assertOk()->assertSee($candidate->display_full_name)->assertDontSee($candidate->email);
        $this->completeOneSop('reviewer', $a, '35.5', $rector);
        $owner = $this->user('dosen');
        $p = Proposal::create(['title' => 'Penelitian', 'type' => 'Penelitian Hibah', 'scheme' => 'Internal', 'year' => 2026, 'user_id' => $owner->user_id, 'status' => 'review', 'grant_stage' => 'review']);
        $this->actingAs($admin)->post(route('grants.records', $p), ['kind' => 'assignment', 'reviewer_id' => $candidate->user_id, 'review_kind' => 'proposal', 'event_date' => today()->toDateString(), 'note' => 'Review keahlian informatika'])->assertRedirect();
        $assignment = $p->grantRecords()->where('kind', 'assignment')->firstOrFail();
        $this->actingAs($candidate)->get(route('grants.show', $p))->assertOk();
        $this->actingAs($candidate)->post(route('grants.records', $p), ['kind' => 'assessment', 'assignment_id' => $assignment->id, 'score' => 88, 'note' => 'Layak didanai', 'criteria_reference' => 'Borang resmi', 'strengths' => 'Baik', 'improvements' => 'Cukup', 'recommendation' => 'approve', 'assessment_form' => $this->file()])->assertRedirect();
        $this->actingAs($owner)->post(route('grants.records', $p), ['kind' => 'assessment', 'assignment_id' => $assignment->id, 'score' => 100, 'note' => 'Menilai sendiri'])->assertForbidden();
        $this->actingAs($admin)->post(route('grants.advance', $p), ['decision' => 'advance'])->assertRedirect();
        $this->assertSame('award', $p->fresh()->grant_stage);
    }

    public function test_unappointed_reviewer_cannot_be_assigned(): void
    {
        $owner = $this->user('dosen');
        $reviewer = $this->user('reviewer');
        $admin = $this->user('lppm_admin');
        $p = Proposal::create(['title' => 'Penelitian', 'type' => 'Penelitian Hibah', 'scheme' => 'Internal', 'year' => 2026, 'user_id' => $owner->user_id, 'status' => 'review', 'grant_stage' => 'review']);
        $this->actingAs($admin)->post(route('grants.records', $p), ['kind' => 'assignment', 'reviewer_id' => $reviewer->user_id, 'review_kind' => 'proposal', 'event_date' => today()->toDateString(), 'note' => 'Tugas'])->assertUnprocessable();
        $this->assertSame(0, GrantRecord::count());
    }

    public function test_grant_call_requires_announcement_and_is_not_manageable_by_researcher(): void
    {
        $owner = $this->user('dosen');
        $admin = $this->user('lppm_admin');
        $data = ['purpose' => 'grant', 'title' => 'Hibah', 'guidelines' => 'Panduan', 'opens_at' => today()->toDateString(), 'closes_at' => today()->addDays(29)->toDateString(), 'published' => 1];
        $this->actingAs($owner)->post(route('grants.calls.store'), $data)->assertForbidden();
        $this->actingAs($admin)->post(route('grants.calls.store'), $data + ['announcement' => $this->file()])->assertRedirect();
        $call = GrantCall::firstOrFail();
        $this->assertFalse($call->published);
        $this->actingAs($admin)->post(route('grants.calls.publish', $call))->assertSessionHasErrors('sop');
        $this->completeSopStage('call', $call, ['admin' => $admin]);
        $this->actingAs($admin)->post(route('grants.calls.publish', $call))->assertSessionHasNoErrors()->assertRedirect();
        $this->actingAs($owner)->get(route('grants.announcement', $call))->assertOk();
    }

    public function test_unpublished_announcement_is_private(): void
    {
        $call = GrantCall::create(['purpose' => 'grant', 'title' => 'Draft rahasia', 'guidelines' => 'Belum terbit', 'opens_at' => today(), 'closes_at' => today()->addDays(30), 'published' => false]);
        $this->get(route('grants.announcements'))->assertOk()->assertDontSee('Draft rahasia');
        $this->get(route('grants.public-announcement', $call))->assertForbidden();
    }
}
