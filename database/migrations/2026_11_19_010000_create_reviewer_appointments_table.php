<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviewer_appointments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('grant_call_id')->nullable()->constrained('grant_calls');
            $t->foreignUuid('user_id')->constrained('users', 'user_id');
            $t->string('origin');
            $t->string('expertise');
            $t->string('institution');
            $t->string('status')->default('registered');
            $t->boolean('published')->default(false);
            $t->string('portfolio_path');
            $t->string('recommendation_path')->nullable();
            $t->string('decree_path')->nullable();
            $t->string('decree_number')->nullable();
            $t->date('valid_until')->nullable();
            $t->json('history')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviewer_appointments');
    }
};
