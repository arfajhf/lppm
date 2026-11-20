<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grant_calls', function (Blueprint $t) {
            $t->text('schedule_exception')->nullable();
        });
        Schema::table('proposals', function (Blueprint $t) {
            $t->string('registration_number', 150)->nullable()->unique();
        });
        Schema::table('reports', function (Blueprint $t) {
            $t->string('report_type', 30)->default('progress');
            $t->uuid('document_id')->nullable();
            $t->json('report_data')->nullable();
            $t->uuid('submitted_by')->nullable();
            $t->uuid('accepted_by')->nullable();
            $t->timestamp('accepted_at')->nullable();
            $t->string('receipt_reference', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reports', fn (Blueprint $t) => $t->dropColumn(['report_type', 'document_id', 'report_data', 'submitted_by', 'accepted_by', 'accepted_at', 'receipt_reference']));
        Schema::table('proposals', function (Blueprint $t) {
            $t->dropUnique(['registration_number']);
            $t->dropColumn('registration_number');
        });
        Schema::table('grant_calls', fn (Blueprint $t) => $t->dropColumn('schedule_exception'));
    }
};
