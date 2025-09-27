<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiCache
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $cacheKey = $this->generateCacheKey($request);

        // Check if response is cached
        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            $response = response()->json($cachedResponse);
            $response->headers->set('X-Cache', 'HIT');
            return $response;
        }

        // Process request
        $response = $next($request);

        // Cache successful responses
        if ($response->getStatusCode() === 200) {
            $responseData = json_decode($response->getContent(), true);
            Cache::put($cacheKey, $responseData, $ttl);
            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }

    /**
     * Generate cache key for the request.
     */
    protected function generateCacheKey(Request $request): string
    {
        $key = 'api:' . $request->path() . ':' . md5($request->getQueryString());
        return $key;
    }
}


