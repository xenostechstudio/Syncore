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
     * Handle Xendit webhook callback.
     *
     * Two security gates:
     *  1) `x-callback-token` header must match `config('xendit.webhook_token')`.
     *  2) When the env is production AND no token is configured, refuse the
     *     request entirely — a missing token in prod is almost always a
     *     misconfiguration that would leave the endpoint open. Dev bypass
     *     stays for local testing but is logged at warning.
     */
    public function __invoke(Request $request)
    {
        $callbackToken   = $request->header('x-callback-token', '');
        $configuredToken = config('xendit.webhook_token');

        if (empty($configuredToken)) {
            if (app()->isProduction()) {
                Log::error('Xendit webhook: refused — production env has no webhook token configured', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['error' => 'Webhook not configured'], 503);
            }
            Log::warning('Xendit webhook: signature verification SKIPPED (no token configured) — non-production only', [
                'env' => app()->environment(),
                'ip'  => $request->ip(),
            ]);
        } elseif (! $this->xenditService->verifyWebhookSignature($callbackToken)) {
            Log::warning('Xendit webhook: invalid callback token', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid callback token'], 401);
        }

        $payload = $request->all();

        // Scrubbed log: header summary only. The full payload contains
        // payment-instrument metadata (channel, paid_amount, customer
        // identifiers from external_id) that aggregators retain and index.
        // Keep behind the local guard for development debugging.
        Log::info('Xendit webhook received', [
            'event'       => $payload['status'] ?? 'unknown',
            'external_id' => $payload['external_id'] ?? 'unknown',
            'paid_amount' => $payload['paid_amount'] ?? 0,
        ]);
        if (app()->environment('local', 'testing')) {
            Log::debug('Xendit webhook payload', ['payload' => $payload]);
        }

        // Empty body or missing external_id — Xendit's "Test endpoint"
        // button. Acknowledge so the dashboard goes green.
        if (empty($payload) || ! isset($payload['external_id'])) {
            return response()->json(['success' => true, 'message' => 'Webhook endpoint is active']);
        }

        // External_id we created elsewhere uses INV-{id}-{timestamp}; if a
        // callback arrives with a different shape it's a Xendit test payload
        // — acknowledge without trying to parse out an invoice id.
        $externalId = $payload['external_id'] ?? '';
        if (! preg_match('/^INV-\d+-/', $externalId)) {
            Log::info('Xendit webhook: test payload acknowledged', ['external_id' => $externalId]);
            return response()->json(['success' => true, 'message' => 'Test webhook received']);
        }

        try {
            $result = $this->xenditService->handleWebhook($payload);

            if ($result) {
                return response()->json(['success' => true]);
            }

            Log::warning('Xendit webhook processing returned false', [
                'external_id' => $externalId,
            ]);
            return response()->json(['error' => 'Failed to process webhook'], 400);
        } catch (\Throwable $e) {
            Log::error('Xendit webhook error', [
                'message'     => $e->getMessage(),
                'external_id' => $externalId,
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
