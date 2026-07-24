<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\CampaignCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $categories = [
                ['slug' => 'signature', 'name' => 'Signature Campaigns', 'display_order' => 1],
                ['slug' => 'upcoming', 'name' => 'Upcoming Campaigns', 'display_order' => 2],
                ['slug' => 'past', 'name' => 'Past Campaigns', 'display_order' => 3],
            ];

            foreach ($categories as $cat) {
                CampaignCategory::updateOrCreate(
                    ['slug' => $cat['slug']],
                    ['name' => $cat['name'], 'display_order' => $cat['display_order']]
                );
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            CampaignCategory::whereIn('slug', ['signature', 'upcoming', 'past'])->delete();
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }
};
