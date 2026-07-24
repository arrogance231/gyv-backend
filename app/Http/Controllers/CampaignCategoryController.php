<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Http\Requests\AssignCategoryRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CampaignCategoryController extends Controller
{
    /**
     * List all categories.
     */
    public function index()
    {
        return response()->json(CampaignCategory::orderBy('display_order', 'asc')->get());
    }

    /**
     * Get campaigns within a category, ordered according to the pivot assignments.
     */
    public function getCategoryCampaigns(Request $request, $slug)
    {
        $user = $request->user('sanctum');
        $featured = $request->query('featured') === 'true';

        // Check if category exists
        $category = CampaignCategory::where('slug', $slug)->firstOrFail();

        // Query campaigns using the scopes
        if ($featured) {
            $query = Campaign::featuredInCategory($slug);
        } else {
            $query = Campaign::orderedInCategory($slug);
        }

        if (!$user) {
            $query->where('campaigns.status', 'published');
        }

        $campaigns = $query->get();

        return response()->json($campaigns);
    }

    /**
     * Assign (or update assignment of) a campaign to a category.
     */
    public function assignCategory(AssignCategoryRequest $request, $campaignId)
    {
        Gate::authorize('update', Campaign::class);

        $campaign = Campaign::findOrFail($campaignId);
        $validated = $request->validated();

        $category = CampaignCategory::where('slug', $validated['category_slug'])->firstOrFail();

        // Perform attach or update
        $campaign->categories()->syncWithoutDetaching([
            $category->id => [
                'order' => $validated['order'] ?? 0,
                'featured' => $validated['featured'] ?? false,
                'featured_order' => $validated['featured_order'] ?? null,
            ]
        ]);

        ActivityLogger::log('assigned_category', 'Campaign', $campaign->id, [
            'category_name' => $category->name,
            'category_slug' => $category->slug,
            'order' => $validated['order'] ?? 0,
            'featured' => $validated['featured'] ?? false
        ]);

        return response()->json([
            'message' => 'Category assigned successfully',
            'campaign' => $campaign->load('categories')
        ]);
    }

    /**
     * Remove campaign from a category.
     */
    public function removeCategory(Request $request, $campaignId, $categoryId)
    {
        Gate::authorize('update', Campaign::class);

        $campaign = Campaign::findOrFail($campaignId);
        $category = CampaignCategory::findOrFail($categoryId);

        $campaign->categories()->detach($categoryId);

        ActivityLogger::log('removed_category', 'Campaign', $campaign->id, [
            'category_name' => $category->name,
            'category_slug' => $category->slug
        ]);

        return response()->json([
            'message' => 'Category assignment removed successfully',
            'campaign' => $campaign->load('categories')
        ]);
    }
}
