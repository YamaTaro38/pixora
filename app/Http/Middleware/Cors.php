<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // Handle OPTIONS preflight request langsung
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept, X-CSRF-TOKEN')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept, X-CSRF-TOKEN');

        return $response;
    }
}