<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * team_id is left nullable at the schema level (no doctrine/dbal
     * dependency is installed to enforce NOT NULL via a follow-up
     * ->change()); every write path in the app sets it going forward
     * (ProjectController::store, SlideEditorController::start), and
     * existing rows are backfilled to their creator's personal team
     * below so no project is left without one in practice.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });

        DB::table('projects')->whereNull('team_id')->orderBy('id')->each(function ($project) {
            $personalTeam = DB::table('teams')
                ->where('user_id', $project->user_id)
                ->where('personal_team', true)
                ->first();

            if ($personalTeam) {
                DB::table('projects')->where('id', $project->id)->update(['team_id' => $personalTeam->id]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });
    }
};
