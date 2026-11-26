<?php

namespace App\Http\Controllers;

use App\Models\InsightAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class InsightAlertController extends Controller
{
    public function markRead(InsightAlert $insightAlert): RedirectResponse
    {
        Gate::authorize('update', $insightAlert);

        $insightAlert->update(['is_read' => true]);

        return back()->with('success', 'Insight marked as read.');
    }

    public function dismiss(InsightAlert $insightAlert): RedirectResponse
    {
        Gate::authorize('update', $insightAlert);

        $insightAlert->update([
            'is_read' => true,
            'dismissed_at' => now(),
        ]);

        return back()->with('success', 'Insight dismissed.');
    }
}
