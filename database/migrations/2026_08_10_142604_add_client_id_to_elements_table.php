<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            // The canvas (Konva) generates its own element ids client-side
            // (e.g. "el-1") before an element is ever persisted. client_id
            // is how a save request's element array is matched back to an
            // existing Element row across saves -- the DB's own auto-
            // increment `id` is never exposed to the client.
            $table->string('client_id')->nullable()->after('slide_id');
            $table->unique(['slide_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->dropUnique(['slide_id', 'client_id']);
            $table->dropColumn('client_id');
        });
    }
};
