<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            if (! Schema::hasColumn('proposals', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('summary');
            }

            if (! Schema::hasColumn('proposals', 'kaprodi_approved_by')) {
                $table->uuid('kaprodi_approved_by')->nullable()->after('submitted_at');
                $table->timestamp('kaprodi_approved_at')->nullable()->after('kaprodi_approved_by');
            }

            if (! Schema::hasColumn('proposals', 'dekan_approved_by')) {
                $table->uuid('dekan_approved_by')->nullable()->after('kaprodi_approved_at');
                $table->timestamp('dekan_approved_at')->nullable()->after('dekan_approved_by');
            }

            if (! Schema::hasColumn('proposals', 'lppm_received_by')) {
                $table->uuid('lppm_received_by')->nullable()->after('dekan_approved_at');
                $table->timestamp('lppm_received_at')->nullable()->after('lppm_received_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $drops = array_values(array_filter([
                Schema::hasColumn('proposals', 'submitted_at') ? 'submitted_at' : null,
                Schema::hasColumn('proposals', 'kaprodi_approved_by') ? 'kaprodi_approved_by' : null,
                Schema::hasColumn('proposals', 'kaprodi_approved_at') ? 'kaprodi_approved_at' : null,
                Schema::hasColumn('proposals', 'dekan_approved_by') ? 'dekan_approved_by' : null,
                Schema::hasColumn('proposals', 'dekan_approved_at') ? 'dekan_approved_at' : null,
                Schema::hasColumn('proposals', 'lppm_received_by') ? 'lppm_received_by' : null,
                Schema::hasColumn('proposals', 'lppm_received_at') ? 'lppm_received_at' : null,
            ]));

            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
