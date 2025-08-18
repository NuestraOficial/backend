<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\UserController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;

class CheckUserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token não fornecido'], 401);
        }

        try {
            $user = $this->getUserFromToken($token);

            if (!$user || !isset($user['id'])) {
                return response()->json(['message' => 'Usuário inválido'], 401);
            }

            if($user['id'] != 5 && $user['id'] != 1){
                return response()->json(['message' => 'Acesso negado'], 403);
            }

            $request->merge([
                'user_id' => $user['id'],
                'user_uuid' => $user['nuestra_uuid'],
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ],
            ]);
            
            return $next($request);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(),], 500);
        }
    }

    private function getUserFromToken(string $token){
        // $isLocal = App::environment('local');

        // if ($isLocal) {
        //     $user = UserController::verifyTokenInternal($token);

        //     if (!$user || empty($user['valid'])) {
        //         throw new \Exception("Token inválido (local)");
        //     }

        //     return $user;
        // }

        $response = Http::withToken($token)->acceptJson()->post(config("app.pacoca_api_url") . "/verify-token");

        if ($response->failed() || empty($response->json('valid'))) {
            throw new \Exception("Token inválido (prod)");
        }

        return $response->json();
    }
}
