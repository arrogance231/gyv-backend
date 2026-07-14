<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * List articles, supporting state filter and category checks.
     */
    public function index(Request $request)
    {
        // Using with('category') to load relationship data
        $query = Article::with('category');

        // Check auth status: public users see only 'published' articles
        $user = $request->user('sanctum');
        if (!$user) {
            $query->where('status', 'published');
        } else {
            // Admin/editor can request specific status or see all
            if ($request->query('status')) {
                $query->where('status', $request->query('status'));
            }
        }

        if ($request->query('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->query('category'));
            });
        }

        return $query->orderBy('published_at', 'desc')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get single article by slug.
     */
    public function show($slug)
    {
        return Article::where('slug', $slug)->with('category')->firstOrFail();
    }

    /**
     * Create article.
     */
    public function store(Request $request)
    {
        if (Gate::denies('manage-articles')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        
        // Ensure slug uniqueness
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Article::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count++;
        }

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $article = Article::create($validated);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article
        ], 201);
    }

    /**
     * Update article details.
     */
    public function update(Request $request, $id)
    {
        if (Gate::denies('manage-articles')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        if ($validated['status'] === 'published' && empty($validated['published_at']) && !$article->published_at) {
            $validated['published_at'] = now();
        }

        $article->update($validated);

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $article
        ]);
    }

    /**
     * Delete article.
     */
    public function destroy($id)
    {
        if (Gate::denies('manage-articles')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully'
        ]);
    }
}
