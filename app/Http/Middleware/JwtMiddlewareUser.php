<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\User;
use ExpiredException;
use JWT;

class JwtMiddlewareUser
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->header('token');

        if (!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'status' => 401,
                'message' => config()['errors'][401]
            ], 401);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch (ExpiredException $e) {
            return response()->json([
                'status' => '401_EXPIRED',
                'message' => config()['errors']['401_EXPIRED']
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => config()['errors'][401]
            ], 401);
        }
        $user = User::findOrFail($credentials->sub);
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;
        return $next($request);
    }
}
