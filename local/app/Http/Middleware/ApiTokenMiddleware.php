<?php

namespace App\Http\Middleware;

use Closure;

class ApiTokenMiddleware
{
	const API_TOKEN = 'arkaryati2018ekspedisi';
	
    public function handle($request, Closure $next)
    {
        // Perform action
    	if($request->get('apiToken') !== md5('arkaryati2018ekspedisi')){
    		return response('Unauthorized.', 401);
    	}

        return $next($request);
    }
}