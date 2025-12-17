<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function __construct(
        protected XenditService $xenditService
    ) {}

    /**
     * Handle Xendit webhook callback
     */
    public function __invoke(Request $request)
    {
        Log::info('Xendit webhook endpoint hit', [
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
        ]);

        // Get the callback token from header
        $callbackToken = $request->header('x-callback-token', '');
        $configuredToken = config('xendit.webhook_token');

        Log::info('Xendit webhook token check', [
            'received_token' => $callbackToken ? 'present' : 'empty',
            'configured_token' => $configuredToken ? 'present' : 'empty',
        ]);

        // Verify webhook signature (skip if no token configured)
        if ($configuredToken && !$this->xenditService->verifyWebhookSignature($callbackToken)) {
            Log::warning('Xendit webhook: invalid callback token', [
                'received' => substr($callbackToken, 0, 10) . '...',
            ]);
            return response()->json(['error' => 'Invalid callback token'], 401);
        }

        $payload = $request->all();

        Log::info('Xendit webhook received', [
            'event' => $payload['status'] ?? 'unknown',
            'external_id' => $payload['external_id'] ?? 'unknown',
            'paid_amount' => $payload['paid_amount'] ?? 0,
            'full_payload' => $payload,
        ]);

        // Handle empty payload or test/validation requests from Xendit
        if (empty($payload) || !isset($payload['external_id'])) {
            Log::info('Xendit webhook: validation/test request received (empty payload)');
            return response()->json(['success' => true, 'message' => 'Webhook endpoint is active']);
        }

        // Handle Xendit test payloads (external_id doesn't match our format INV-{id}-{timestamp})
        $externalId = $payload['external_id'] ?? '';
        if (!preg_match('/^INV-\d+-/', $externalId)) {
            Log::info('Xendit webhook: test payload received', ['external_id' => $externalId]);
            return response()->json(['success' => true, 'message' => 'Test webhook received']);
        }

        try {
            $result = $this->xenditService->handleWebhook($payload);

            if ($result) {
                Log::info('Xendit webhook processed successfully', [
                    'external_id' => $payload['external_id'] ?? 'unknown',
                ]);
                return response()->json(['success' => true]);
            }

            Log::warning('Xendit webhook processing returned false', [
                'external_id' => $payload['external_id'] ?? 'unknown',
            ]);
            return response()->json(['error' => 'Failed to process webhook'], 400);
        } catch (\Exception $e) {
            Log::error('Xendit webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
