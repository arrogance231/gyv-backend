<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * List paginated activity logs.
     * Requires authentication.
     */
    public function index(Request $request)
    {
        // Accessible by any authenticated dashboard user
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json($logs);
    }
}
