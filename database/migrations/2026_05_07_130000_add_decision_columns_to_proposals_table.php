<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            if (! Schema::hasColumn('proposals', 'last_decision_stage')) {
                $table->string('last_decision_stage', 30)->nullable()->after('lppm_received_at');
            }

            if (! Schema::hasColumn('proposals', 'last_decision_type')) {
                $table->string('last_decision_type', 30)->nullable()->after('last_decision_stage');
            }

            if (! Schema::hasColumn('proposals', 'last_decision_note')) {
                $table->text('last_decision_note')->nullable()->after('last_decision_type');
            }

            if (! Schema::hasColumn('proposals', 'last_decision_by')) {
                $table->uuid('last_decision_by')->nullable()->after('last_decision_note');
            }

            if (! Schema::hasColumn('proposals', 'last_decision_at')) {
                $table->timestamp('last_decision_at')->nullable()->after('last_decision_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $drops = array_values(array_filter([
                Schema::hasColumn('proposals', 'last_decision_stage') ? 'last_decision_stage' : null,
                Schema::hasColumn('proposals', 'last_decision_type') ? 'last_decision_type' : null,
                Schema::hasColumn('proposals', 'last_decision_note') ? 'last_decision_note' : null,
                Schema::hasColumn('proposals', 'last_decision_by') ? 'last_decision_by' : null,
                Schema::hasColumn('proposals', 'last_decision_at') ? 'last_decision_at' : null,
            ]));

            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
