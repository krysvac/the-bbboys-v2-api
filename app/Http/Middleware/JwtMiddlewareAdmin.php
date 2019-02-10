<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\User;
use ExpiredException;
use JWT;

class JwtMiddlewareAdmin
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
                'status' => 401,
                'message' => "The provided authentication token has expired!"
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => config()['errors'][401]
            ], 401);
        }
        $user = User::findOrFail($credentials->sub);
        $request->auth = $user;

        if ((string)$user["isAdmin"] === "1") {
            // Now let's put the user in the request class so that you can grab it from there
            return $next($request);
        } else {
            return response()->json([
                'status' => 401,
                'message' => config()['errors'][401]
            ], 401);
        }
    }
}
