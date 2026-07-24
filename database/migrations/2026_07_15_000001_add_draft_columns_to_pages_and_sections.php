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
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('has_draft')->default(false);
        });

        Schema::table('page_sections', function (Blueprint $table) {
            $table->json('draft_content')->nullable();
            $table->timestamp('draft_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropColumn(['draft_content', 'draft_updated_at']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('has_draft');
        });
    }
};
