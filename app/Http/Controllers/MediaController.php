<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * List uploaded media assets.
     */
    public function index()
    {
        if (Gate::denies('manage-media')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return Media::orderBy('created_at', 'desc')->get();
    }

    /**
     * Upload an asset file.
     */
    public function store(Request $request)
    {
        if (Gate::denies('manage-media')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'file' => 'required|file|image|max:10240', // Limit to images up to 10MB
        ]);

        $file = $request->file('file');
        
        // Store in the local public folder
        $path = $file->store('uploads', 'public');
        
        // Generate the URL path
        $url = asset('storage/' . $path);

        $media = Media::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'url' => $url,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'media' => $media,
        ], 201);
    }

    /**
     * Delete an asset.
     */
    public function destroy($id)
    {
        if (Gate::denies('manage-media')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $media = Media::findOrFail($id);

        // Delete the physical file if it exists
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media file deleted successfully',
        ]);
    }
}
