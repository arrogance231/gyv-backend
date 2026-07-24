<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CampaignController extends Controller
{
    /**
     * List all campaigns.
     * Public users see only published campaigns.
     * Admins/editors can filter by status or category.
     */
    public function index(Request $request)
    {
        $query = Campaign::with('categories');

        // Check if public or authenticated
        $user = $request->user('sanctum');

        if (!$user) {
            $query->published();
        } else {
            // Support showing archived (soft deleted) campaigns for management
            if ($request->query('include_archived') === 'true') {
                $query->withTrashed();
            }

            if ($request->query('status')) {
                $query->where('status', $request->query('status'));
            }
        }

        if ($request->query('category')) {
            $query->inCategory($request->query('category'));
        }

        // Default ordering
        $campaigns = $query->orderBy('created_at', 'desc')->get();

        return response()->json($campaigns);
    }

    /**
     * Store a newly created campaign in storage.
     */
    public function store(StoreCampaignRequest $request)
    {
        Gate::authorize('create', Campaign::class);

        $validated = $request->validated();
        $campaign = Campaign::create($validated);

        ActivityLogger::log('created', 'Campaign', $campaign->id, [
            'title' => $campaign->title,
            'slug' => $campaign->slug,
            'changed_fields' => array_keys($validated)
        ]);

        return response()->json([
            'message' => 'Campaign created successfully',
            'campaign' => $campaign->load('categories')
        ], 201);
    }

    /**
     * Display the specified campaign.
     * Public requests must be published.
     */
    public function show(Request $request, $slug)
    {
        $user = $request->user('sanctum');
        $query = Campaign::with('categories');

        if ($user) {
            $query->withTrashed();
        } else {
            $query->published();
        }

        // Support lookup by ID or slug
        $campaign = is_numeric($slug) 
            ? $query->where('id', $slug)->firstOrFail()
            : $query->where('slug', $slug)->firstOrFail();

        return response()->json($campaign);
    }

    /**
     * Update the specified campaign in storage.
     */
    public function update(UpdateCampaignRequest $request, $id)
    {
        Gate::authorize('update', Campaign::class);

        $campaign = Campaign::withTrashed()->findOrFail($id);
        $validated = $request->validated();

        // Check which fields changed
        $changedFields = [];
        foreach ($validated as $key => $value) {
            if ($campaign->$key != $value) {
                $changedFields[] = $key;
            }
        }

        $campaign->update($validated);

        ActivityLogger::log('updated', 'Campaign', $campaign->id, [
            'title' => $campaign->title,
            'slug' => $campaign->slug,
            'changed_fields' => $changedFields
        ]);

        return response()->json([
            'message' => 'Campaign updated successfully',
            'campaign' => $campaign->load('categories')
        ]);
    }

    /**
     * Remove the specified campaign (Soft Delete = Archive).
     */
    public function destroy(Request $request, $id)
    {
        Gate::authorize('delete', Campaign::class);

        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        ActivityLogger::log('archived', 'Campaign', $campaign->id, [
            'title' => $campaign->title,
            'slug' => $campaign->slug
        ]);

        return response()->json([
            'message' => 'Campaign archived successfully'
        ]);
    }

    /**
     * Restore the specified soft-deleted campaign.
     */
    public function restore(Request $request, $id)
    {
        Gate::authorize('restore', Campaign::class);

        $campaign = Campaign::onlyTrashed()->findOrFail($id);
        $campaign->restore();

        ActivityLogger::log('restored', 'Campaign', $campaign->id, [
            'title' => $campaign->title,
            'slug' => $campaign->slug
        ]);

        return response()->json([
            'message' => 'Campaign restored successfully',
            'campaign' => $campaign->load('categories')
        ]);
    }
}
