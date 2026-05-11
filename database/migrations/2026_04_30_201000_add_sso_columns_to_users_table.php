<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sso_user_id')->nullable()->unique()->after('user_id');
            $table->string('username')->nullable()->after('sso_user_id');
            $table->string('full_name')->nullable()->after('username');
            $table->string('avatar')->nullable()->after('study_program');
            $table->boolean('is_admin')->default(false)->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_user_id', 'username', 'full_name', 'avatar', 'is_admin']);
        });
    }
};
