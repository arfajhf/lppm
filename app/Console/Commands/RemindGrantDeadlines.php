<?php

namespace App\Console\Commands;

use App\Models\ActivityNotification;
use App\Models\GrantCall;
use App\Models\GrantRecord;
use App\Models\Proposal;
use App\Models\ReviewerAppointment;
use App\Models\User;
use App\Services\GrantSop;
use App\Services\GrantWorkflow;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RemindGrantDeadlines extends Command
{
    protected $signature = 'grants:remind';

    protected $description = 'Kirim pengingat tenggat hibah di aplikasi, maksimal satu kali per hari per tahap.';

    public function handle(): int
    {
        Proposal::whereNotNull('grant_stage')->whereNotIn('grant_stage', ['completed', 'rejected'])->whereNotNull('stage_due_at')->where('stage_due_at', '<=', now()->addDays(3))->chunkById(100, function ($proposals) {
            foreach ($proposals as $p) {
                $message = 'Tenggat '.(GrantWorkflow::STAGES[$p->grant_stage] ?? $p->grant_stage).' untuk '.$p->title.': '.$p->stage_due_at->format('d M Y');
                if (! ActivityNotification::where('user_id', $p->user_id)->where('channel', 'hibah-reminder')->where('message', $message)->whereDate('created_at', today())->exists()) {
                    ActivityNotification::create(['user_id' => $p->user_id, 'message' => $message, 'channel' => 'hibah-reminder']);
                }
            }
        }, 'proposal_id');

        $sop = app(GrantSop::class);
        $users = User::all();
        foreach (['proposal' => Proposal::whereNotNull('grant_stage')->whereNotIn('grant_stage', ['completed', 'rejected']), 'reviewer' => ReviewerAppointment::query(), 'call' => GrantCall::query()] as $scope => $query) {
            $query->chunk(100, function ($subjects) use ($scope, $sop, $users) {
                foreach ($subjects as $subject) {
                    foreach ($sop->definitions($scope, $subject) as $key => $d) {
                        if ($d['stage'] !== $sop->stage($scope, $subject) || $sop->action($scope, $subject, $key)) {
                            continue;
                        }
                        $due = $sop->dueAt($scope, $subject, $key);
                        if (! $due || $due->gt(now()->addDays(3))) {
                            continue;
                        }
                        foreach ($users as $user) {
                            if (! $sop->allowed($scope, $subject, $d, $user)) {
                                continue;
                            }
                            $message = 'Tenggat SOP '.$key.' — '.$d['title'].' ('.$scope.' '.$subject->getKey().'): '.$due->format('d M Y');
                            if (! ActivityNotification::where('user_id', $user->user_id)->where('channel', 'sop-reminder')->where('message', $message)->whereDate('created_at', today())->exists()) {
                                ActivityNotification::create(['user_id' => $user->user_id, 'channel' => 'sop-reminder', 'message' => $message]);
                            }
                        }
                    }
                }
            });
        }

        GrantRecord::where('kind', 'assignment')->get()->each(function ($assignment) {
            $due = $assignment->data['assessment_due_at'] ?? null;
            if (! $due || Carbon::parse($due)->gt(now()->addDays(3))) {
                return;
            }
            $p = Proposal::find($assignment->proposal_id);
            if (! $p || in_array($p->grant_stage, ['completed', 'rejected'])) {
                return;
            }
            if ($p->grantRecords()->where('kind', 'assessment')->where('data->assignment_id', $assignment->id)->exists()) {
                return;
            }
            $reviewerId = $assignment->data['reviewer_id'];
            $message = 'Tenggat penilaian tugas #'.$assignment->id.' ('.($assignment->data['deadline_source'] ?? 'jadwal resmi').') untuk '.$p->title.': '.Carbon::parse($due)->format('d M Y');
            if (! ActivityNotification::where('user_id', $reviewerId)->where('channel', 'sop-reminder')->where('message', $message)->whereDate('created_at', today())->exists()) {
                ActivityNotification::create(['user_id' => $reviewerId, 'channel' => 'sop-reminder', 'message' => $message]);
            }
        });

        return self::SUCCESS;
    }
}
