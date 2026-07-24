<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a CMS activity.
     */
    public static function log(string $action, string $modelType, $modelId, ?array $metadata = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => (string) $modelId,
                'metadata' => $metadata,
                'ip_address' => Request::ip(),
            ]);
        } catch (\Throwable $e) {
            // Silence error to prevent database issues from breaking core workflows
            logger()->error('Failed to write activity log', [
                'exception' => $e->getMessage(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId
            ]);
        }
    }
}
