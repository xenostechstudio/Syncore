<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limiterName = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $limiterName);
        $maxAttempts = $this->getMaxAttempts($limiterName);
        $decayMinutes = $this->getDecayMinutes($limiterName);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders(
            $response,
            $maxAttempts,
            RateLimiter::remaining($key, $maxAttempts)
        );
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, string $limiterName): string
    {
        $user = $request->user();
        
        if ($user) {
            return "{$limiterName}:{$user->id}";
        }

        return "{$limiterName}:{$request->ip()}";
    }

    /**
     * Get max attempts for the limiter.
     */
    protected function getMaxAttempts(string $limiterName): int
    {
        return match ($limiterName) {
            'api' => 60,           // 60 requests per minute
            'api-heavy' => 10,    // 10 requests per minute for heavy operations
            'api-export' => 5,    // 5 exports per minute
            default => 60,
        };
    }

    /**
     * Get decay minutes for the limiter.
     */
    protected function getDecayMinutes(string $limiterName): int
    {
        return match ($limiterName) {
            'api-export' => 5,
            default => 1,
        };
    }

    /**
     * Build too many attempts response.
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $retryAfter,
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addRateLimitHeaders(Response $response, int $maxAttempts, int $remaining): Response
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }
}
