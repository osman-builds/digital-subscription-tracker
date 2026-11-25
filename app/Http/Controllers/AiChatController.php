<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Services\AiChatService;
use App\Services\FinancialIntelligenceService;
use App\Services\SandboxSubscriptionStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    /**
     * Handle public AI concierge inquiries from landing page and sandbox launchpad.
     */
    public function concierge(Request $request, AiChatService $aiChatService): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:250'],
        ]);

        $result = $aiChatService->askConcierge($validated['query']);

        return response()->json([
            'ok' => true,
            'reply' => $result['reply'],
            'cached' => $result['cached'],
            'flagged' => $result['flagged'],
        ]);
    }

    /**
     * Handle personal AI copilot inquiries from logged-in users and active sandbox sessions.
     */
    public function copilot(
        Request $request,
        AiChatService $aiChatService,
        SandboxSubscriptionStore $sandboxSubscriptions,
        FinancialIntelligenceService $intelligenceService
    ): JsonResponse {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:250'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $userId = $user->id;
        $settings = $user->getSettings();

        if ($user->isSandbox()) {
            $subscriptions = $sandboxSubscriptions->active($userId);
        } else {
            $subscriptions = Subscription::with('category')
                ->forUser($userId)
                ->active()
                ->get();
        }

        $intelligence = $intelligenceService->analyze($user, $subscriptions, $settings);

        $result = $aiChatService->askCopilot(
            $user,
            $validated['query'],
            $subscriptions,
            $settings,
            $intelligence
        );

        return response()->json([
            'ok' => true,
            'reply' => $result['reply'],
            'flagged' => $result['flagged'],
        ]);
    }
}
