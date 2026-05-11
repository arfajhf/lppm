<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_members', function (Blueprint $table) {
            $table->uuid('member_id')->primary();
            $table->foreignUuid('proposal_id')->constrained('proposals', 'proposal_id')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('role', 50)->default('anggota');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_members');
    }
};
