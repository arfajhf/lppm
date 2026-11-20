<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_actions', function (Blueprint $t) {
            $t->id();
            $t->string('scope', 20);
            $t->string('subject_id', 64);
            $t->string('step_key', 100);
            $t->foreignUuid('actor_id')->constrained('users', 'user_id');
            $t->json('fields');
            $t->json('files');
            $t->unsignedBigInteger('cycle')->default(0);
            $t->timestamp('due_at')->nullable();
            $t->timestamps();
            $t->index(['scope', 'subject_id', 'step_key', 'cycle'], 'sop_subject_step_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_actions');
    }
};
