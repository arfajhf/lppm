<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('review_id')->primary();
            $table->foreignUuid('proposal_id')->constrained('proposals', 'proposal_id')->cascadeOnDelete();
            $table->foreignUuid('reviewer_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->unsignedDecimal('score', 5, 2)->nullable();
            $table->text('comment')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
