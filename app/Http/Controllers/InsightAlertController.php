<?php

namespace App\Http\Controllers;

use App\Models\InsightAlert;
use Illuminate\Http\RedirectResponse;

class InsightAlertController extends Controller
{
    public function markRead(InsightAlert $insightAlert): RedirectResponse
    {
        abort_if($insightAlert->user_id !== auth()->id(), 403);

        $insightAlert->update(['is_read' => true]);

        return back()->with('success', 'Insight marked as read.');
    }

    public function dismiss(InsightAlert $insightAlert): RedirectResponse
    {
        abort_if($insightAlert->user_id !== auth()->id(), 403);

        $insightAlert->update([
            'is_read' => true,
            'dismissed_at' => now(),
        ]);

        return back()->with('success', 'Insight dismissed.');
    }
}
