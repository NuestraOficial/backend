<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = explode(',', config('app.frontend_urls'));
        $allowedIps = explode(',', config('app.allowed_ips'));
        $uptimeRobotIps = explode(',', config('app.uptimerobot_ips'));
        $allAllowedIps = array_merge($allowedIps, $uptimeRobotIps);
        $origin = $request->headers->get('Origin');
        $clientIp = $request->ip(); // Obtém o IP do cliente

        // se não tiver origin (navegador/postman) não permite fazer a menos que esteja com ip permitido
        if (!$origin && !in_array($clientIp, $allAllowedIps)) {
            return response()->json(['message' => 'CORS: Seu IP nao esta aqui' ], 403);
        }
        
        $response = $next($request);
        if($origin){
            if (in_array($origin, $allowedOrigins)) {
                $response->header('Access-Control-Allow-Origin', $origin);
            }else{
                return response()->json(['message' => "CORS: Seu site nao esta aqui" . $origin], 403);
            }

        }

        return $response
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
    }
}
