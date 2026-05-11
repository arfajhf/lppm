<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('partner_id')->primary();
            $table->string('name');
            $table->string('institution_type', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('proposal_partner', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('proposal_id')->constrained('proposals', 'proposal_id')->cascadeOnDelete();
            $table->foreignUuid('partner_id')->constrained('partners', 'partner_id')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['proposal_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_partner');
        Schema::dropIfExists('partners');
    }
};
