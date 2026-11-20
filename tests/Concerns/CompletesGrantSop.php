<?php

namespace Tests\Concerns;

use App\Models\GrantCall;
use App\Models\ReviewerAppointment;
use App\Models\User;
use App\Services\GrantSop;
use Illuminate\Http\UploadedFile;

trait CompletesGrantSop
{
    protected function sopPayload(array $d, $subject): array
    {
        $fields = [];
        foreach ($d['fields'] as $key => $f) {
            $fields[$key] = str_contains($f['rule'], 'date') ? (str_contains($f['rule'], 'after_or_equal') ? today()->addYear()->toDateString() : today()->toDateString()) : (str_contains($f['rule'], 'integer') ? 1 : 'Bukti resmi '.$key);
            if ($key === 'output_type') {
                $fields[$key] = 'journal';
            }
            if ($key === 'output_url') {
                $fields[$key] = 'https://example.org/publication';
            }
            if ($key === 'action') {
                $fields[$key] = 'maintenance';
            }
            if ($key === 'amount') {
                $fields[$key] = $d['stage'] === 'payment70' ? intdiv($subject->grant_amount * 70, 100) : $subject->grant_amount - intdiv($subject->grant_amount * 70, 100);
            }
        }
        $files = [];
        foreach ($d['documents'] as $kind => $label) {
            $files[$kind] = UploadedFile::fake()->create($kind.'.pdf', 10, 'application/pdf');
        }

        return ['fields' => $fields, 'checks' => array_fill_keys(array_keys($d['checks']), 1), 'files' => $files];
    }

    protected function completeOneSop(string $scope, $subject, string $key, User $actor): void
    {
        $d = config('grant_sop')[$key];
        $this->actingAs($actor)->post(route('grant-sop.store', [$scope, $subject->getKey(), $key]), $this->sopPayload($d, $subject))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNotNull(app(GrantSop::class)->action($scope, $subject, $key));
    }

    protected function appointReviewer(User $candidate, User $admin, User $dean, User $rector): void
    {
        $call = GrantCall::create(['purpose' => 'reviewer', 'title' => 'Seleksi reviewer', 'guidelines' => 'Kriteria resmi', 'opens_at' => today(), 'closes_at' => today()->addDays(13), 'published' => true]);
        $this->actingAs($candidate)->post(route('reviewer-appointments.store'), ['grant_call_id' => $call->id, 'origin' => 'internal', 'expertise' => 'Informatika', 'institution' => 'UMB', 'portfolio' => UploadedFile::fake()->create('portfolio.pdf', 10, 'application/pdf')])->assertSessionHasNoErrors()->assertRedirect();
        $a = ReviewerAppointment::where('user_id', $candidate->user_id)->latest('id')->firstOrFail();
        $actors = ['admin' => $admin, 'dean_wr' => $dean, 'rektor' => $rector];
        foreach ([$dean, $admin, $rector] as $actor) {
            $this->completeSopStage('reviewer', $a, $actors);
            $this->actingAs($actor)->post(route('reviewer-appointments.advance', $a), ['note' => 'Dokumen dan kelayakan diperiksa'])->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->completeSopStage('reviewer', $a, $actors);
        $this->actingAs($admin)->post(route('reviewer-appointments.publish', $a))->assertSessionHasNoErrors()->assertRedirect();
        $this->completeOneSop('reviewer', $a, '35.5', $rector);
        $this->assertTrue($a->fresh()->readyForAssignment());
    }

    protected function completeSopStage(string $scope, $subject, array $actors): void
    {
        $subject = $subject->fresh();
        $sop = app(GrantSop::class);
        foreach ($sop->definitions($scope, $subject) as $key => $d) {
            if ($d['stage'] !== $sop->stage($scope, $subject) || ! $d['gate'] || $sop->action($scope, $subject, $key)) {
                continue;
            }
            $actor = $d['role'] === 'secretariat' ? ($actors['secretariat'] ?? $actors['admin']) : $actors[$d['role']];
            $this->actingAs($actor)->post(route('grant-sop.store', [$scope, $subject->getKey(), $key]), $this->sopPayload($d, $subject))->assertSessionHasNoErrors()->assertRedirect();
            $this->assertNotNull($sop->action($scope, $subject, $key), 'SOP '.$key.' must be recorded');
        }
    }
}
