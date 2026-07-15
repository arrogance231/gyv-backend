<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
     * Accessible publicly.
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->with('sections')->firstOrFail();
        
        // Key sections by section_key for simple frontend object mapping
        $sections = $page->sections->pluck('content', 'section_key');

        return response()->json([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'sections' => $sections,
            'raw_sections' => $page->sections // useful for edit reference ids
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

        return response()->json([
            'message' => 'Page SEO settings updated successfully',
            'page' => $page,
        ]);
    }

    /**
     * Update page section JSON content.
     */
    public function updateSection(Request $request, $id)
    {
        if (Gate::denies('edit-site-sections')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $section = PageSection::findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|array',
            'title' => 'sometimes|required|string|max:255'
        ]);

        $section->update($validated);

        return response()->json([
            'message' => 'Section content updated successfully',
            'section' => $section,
        ]);
    }

    /**
     * Create a new repeatable page section (e.g. an additional declaration block).
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

        $section = PageSection::create($validated);

        return response()->json([
            'message' => 'Section created successfully',
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

        $section = PageSection::findOrFail($id);
        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully',
        ]);
    }
}
