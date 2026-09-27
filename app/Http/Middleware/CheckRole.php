<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckRole
{
    /**
     * @param  string  ...$roles  Allowed roles, e.g. CUSTOMER, FARMER
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User information not found.',
                ], 404);
            }

            if ($roles !== [] && ! in_array($user->role, $roles, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }
        } catch (TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired, please sign in again.',
            ], 401);
        } catch (TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token.',
            ], 401);
        } catch (JWTException) {
            return response()->json([
                'success' => false,
                'message' => 'Token is missing or could not be decoded.',
            ], 401);
        }

        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
