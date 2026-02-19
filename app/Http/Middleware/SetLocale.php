<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Set locale from session
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        } 
        // Or from user preference
        elseif (auth()->check() && auth()->user()->language) {
            $locale = auth()->user()->language;
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
