<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('document_id')->primary();
            $table->foreignUuid('proposal_id')->constrained('proposals', 'proposal_id')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('document_type', 50)->default('proposal');
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
