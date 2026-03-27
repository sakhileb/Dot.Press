<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table): void {
            $table->unsignedInteger('revision')->default(0)->after('canvas_state');
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table): void {
            $table->dropColumn('revision');
        });
    }
};
