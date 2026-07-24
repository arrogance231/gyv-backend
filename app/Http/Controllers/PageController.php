<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageVersion;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * List all pages.
     */
    public function index()
    {
        return Page::with('sections')->get();
    }

    /**
     * Show a page by slug with its sections.
     * Accessible publicly (shows published content) or by admin (shows draft content).
     */
    public function show(Request $request, $slug)
    {
        $page = Page::where('slug', $slug)->with('sections')->firstOrFail();
        $user = $request->user('sanctum');

        // Key sections by section_key
        $sections = $page->sections->mapWithKeys(function ($section) use ($user) {
            $content = ($user && $section->draft_content !== null) 
                ? $section->draft_content 
                : $section->content;
            return [$section->section_key => $content];
        });

        // Map raw sections for admin editor
        $rawSections = $page->sections->map(function ($section) use ($user) {
            $clone = clone $section;
            if ($user && $section->draft_content !== null) {
                $clone->content = $section->draft_content;
            }
            return $clone;
        });

        return response()->json([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'has_draft' => $page->has_draft,
            'sections' => $sections,
            'raw_sections' => $rawSections
        ]);
    }

    /**
     * Update page details and SEO metadata.
     */
    public function update(Request $request, $id)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $page->update($validated);

        ActivityLogger::log('updated', 'Page', $page->id, [
            'page_slug' => $page->slug,
            'changed_fields' => array_keys($validated)
        ]);

        return response()->json([
            'message' => 'Page SEO settings updated successfully',
            'page' => $page,
        ]);
    }

    /**
     * Update page section JSON content (saves as draft).
     */
    public function updateSection(Request $request, $id)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $section = PageSection::with('page')->findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|array',
            'title' => 'sometimes|required|string|max:255'
        ]);

        $changedFields = [];
        $oldContent = $section->draft_content ?? $section->content ?? [];
        foreach ($validated['content'] as $k => $v) {
            if (!isset($oldContent[$k]) || $oldContent[$k] !== $v) {
                $changedFields[] = $k;
            }
        }

        $section->draft_content = $validated['content'];
        $section->draft_updated_at = now();
        if ($request->has('title')) {
            $section->title = $validated['title'];
        }
        $section->save();

        // Mark page as having draft
        $section->page->update(['has_draft' => true]);

        ActivityLogger::log('saved_draft', 'Page', $section->page_id, [
            'page_slug' => $section->page->slug,
            'section_key' => $section->section_key,
            'changed_fields' => $changedFields
        ]);

        return response()->json([
            'message' => 'Section draft updated successfully',
            'section' => $section,
        ]);
    }

    /**
     * Create a new repeatable page section (saves as draft).
     */
    public function storeSection(Request $request)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'section_key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('page_sections')->where('page_id', $request->page_id),
            ],
            'title' => 'required|string|max:255',
            'content' => 'required|array',
        ]);

        $section = PageSection::create([
            'page_id' => $validated['page_id'],
            'section_key' => $validated['section_key'],
            'title' => $validated['title'],
            'content' => [], // Empty published state
            'draft_content' => $validated['content'],
            'draft_updated_at' => now(),
        ]);

        $page = Page::findOrFail($validated['page_id']);
        $page->update(['has_draft' => true]);

        ActivityLogger::log('saved_draft', 'Page', $page->id, [
            'page_slug' => $page->slug,
            'section_key' => $section->section_key,
            'action_type' => 'created_repeatable_section'
        ]);

        return response()->json([
            'message' => 'Section created as draft successfully',
            'section' => $section,
        ], 201);
    }

    /**
     * Remove a repeatable page section.
     */
    public function destroySection($id)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $section = PageSection::with('page')->findOrFail($id);
        $page = $section->page;
        $sectionKey = $section->section_key;
        
        $section->delete();

        $page->update(['has_draft' => true]);

        ActivityLogger::log('saved_draft', 'Page', $page->id, [
            'page_slug' => $page->slug,
            'section_key' => $sectionKey,
            'action_type' => 'deleted_repeatable_section'
        ]);

        return response()->json([
            'message' => 'Section deleted successfully',
        ]);
    }

    /**
     * Publish draft content to live (promote draft_content to content).
     * Creates a new PageVersion snapshot.
     */
    public function publish(Request $request, $id)
    {
        if (Gate::denies('publish-content')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $page = Page::findOrFail($id);
        $user = $request->user();

        DB::transaction(function () use ($page, $user) {
            foreach ($page->sections as $section) {
                if ($section->draft_content !== null) {
                    $section->content = $section->draft_content;
                    $section->draft_content = null;
                    $section->draft_updated_at = null;
                    $section->save();
                }
            }
            $page->has_draft = false;
            $page->save();

            // Create page version snapshot
            PageVersion::create([
                'page_id' => $page->id,
                'created_by' => $user->id,
                'snapshot' => [
                    'page_slug' => $page->slug,
                    'sections' => $page->fresh()->sections->pluck('content', 'section_key'),
                ],
            ]);
        });

        ActivityLogger::log('published', 'Page', $page->id, [
            'page_slug' => $page->slug
        ]);

        return response()->json([
            'message' => 'Page drafts published successfully',
            'page' => $page->load('sections')
        ]);
    }

    /**
     * Generate a signed URL for draft preview.
     */
    public function getPreviewUrl(Request $request, $slug)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        // Generate temporary signed URL (valid for 1 hour)
        $url = URL::temporarySignedRoute(
            'preview.page',
            now()->addHours(1),
            ['slug' => $slug]
        );

        return response()->json([
            'preview_url' => $url
        ]);
    }

    /**
     * Serve draft preview data (requires a valid signed URL).
     */
    public function previewPage(Request $request, $slug)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired preview signature.');
        }

        $page = Page::where('slug', $slug)->with('sections')->firstOrFail();

        // Key sections by section_key using draft_content if available, falling back to content
        $sections = $page->sections->mapWithKeys(function ($section) {
            $content = $section->draft_content !== null ? $section->draft_content : $section->content;
            return [$section->section_key => $content];
        });

        return response()->json([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'sections' => $sections,
            'is_preview' => true
        ]);
    }

    /**
     * List versions of a page.
     */
    public function versions($pageId)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $versions = PageVersion::where('page_id', $pageId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($versions);
    }

    /**
     * Restore a specific page version snapshot as draft.
     */
    public function restoreVersion(Request $request, $versionId)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $version = PageVersion::findOrFail($versionId);
        $page = Page::findOrFail($version->page_id);

        DB::transaction(function () use ($page, $version) {
            $snapshot = $version->snapshot;
            $sectionsData = $snapshot['sections'] ?? [];

            foreach ($sectionsData as $key => $content) {
                $section = PageSection::where('page_id', $page->id)
                    ->where('section_key', $key)
                    ->first();

                if ($section) {
                    $section->draft_content = $content;
                    $section->draft_updated_at = now();
                    $section->save();
                }
            }

            $page->has_draft = true;
            $page->save();
        });

        ActivityLogger::log('restored', 'Page', $page->id, [
            'page_slug' => $page->slug,
            'version_id' => $version->id,
            'version_created_at' => $version->created_at->toIso8601String()
        ]);

        return response()->json([
            'message' => 'Page version restored to drafts successfully',
            'page' => $page->load('sections')
        ]);
    }
}
