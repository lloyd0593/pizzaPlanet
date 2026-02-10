<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log an activity to both the database and Laravel log.
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $details = []
    ): ActivityLog {
        $userId = Auth::id();
        $sessionId = session()->getId();

        // Write to Laravel log channel
        Log::channel('activity')->info($action, [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'ip_address' => Request::ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Write to database
        return ActivityLog::create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'details' => $details,
            'ip_address' => Request::ip(),
        ]);
    }
}
