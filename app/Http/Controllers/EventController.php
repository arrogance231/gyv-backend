<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * List events, filtering published vs all based on authentication context.
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Check authentication state
        $user = $request->user('sanctum');
        if (!$user) {
            $query->where('status', 'published');
        } else {
            if ($request->query('status')) {
                $query->where('status', $request->query('status'));
            }
        }

        return $query->orderBy('start_time', 'asc')->get();
    }

    /**
     * Retrieve a specific event.
     */
    public function show($id)
    {
        return Event::findOrFail($id);
    }

    /**
     * Create a new event.
     */
    public function store(Request $request)
    {
        if (Gate::denies('manage-events')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'registration_link' => 'nullable|url',
            'image_url' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event
        ], 201);
    }

    /**
     * Update an event.
     */
    public function update(Request $request, $id)
    {
        if (Gate::denies('manage-events')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'registration_link' => 'nullable|url',
            'image_url' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }

    /**
     * Delete an event.
     */
    public function destroy($id)
    {
        if (Gate::denies('manage-events')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
