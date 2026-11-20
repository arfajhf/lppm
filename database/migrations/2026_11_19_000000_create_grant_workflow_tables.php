<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grant_calls', function (Blueprint $t) {
            $t->id();
            $t->string('purpose')->default('grant');
            $t->string('announcement_path')->nullable();
            $t->string('title');
            $t->text('guidelines');
            $t->date('opens_at');
            $t->date('closes_at');
            $t->boolean('published')->default(false);
            $t->timestamps();
        });
        Schema::table('proposals', function (Blueprint $t) {
            $t->foreignId('grant_call_id')->nullable()->constrained('grant_calls');
            $t->string('grant_stage', 50)->nullable();
            $t->string('revision_stage', 50)->nullable();
            $t->unsignedBigInteger('grant_amount')->nullable();
            $t->timestamp('stage_due_at')->nullable();
        });
        Schema::create('grant_records', function (Blueprint $t) {
            $t->id();
            $t->foreignUuid('proposal_id')->constrained('proposals', 'proposal_id')->cascadeOnDelete();
            $t->foreignUuid('actor_id')->constrained('users', 'user_id');
            $t->string('kind', 50);
            $t->string('from_stage', 50)->nullable();
            $t->string('to_stage', 50)->nullable();
            $t->text('note')->nullable();
            $t->json('data')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grant_records');
        Schema::table('proposals', function (Blueprint $t) {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                $t->dropColumn('grant_call_id');
            } else {
                $t->dropConstrainedForeignId('grant_call_id');
            }
            $t->dropColumn(['grant_stage', 'revision_stage', 'grant_amount', 'stage_due_at']);
        });
        Schema::dropIfExists('grant_calls');
    }
};
