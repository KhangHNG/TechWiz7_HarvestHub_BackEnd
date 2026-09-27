<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api', 'api/*') || $request->expectsJson();
        });

        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->is('api', 'api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The verification link is invalid or has expired.',
                ], 403);
            }

            return response()->view('auth.email-verified', [
                'success' => false,
                'message' => 'The verification link is invalid or has expired.',
            ], 403);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api', 'api/*') || $e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data.',
                    'errors' => $e->errors(),
                ], 422);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            if ($e instanceof AuthenticationException) {
                $status = 401;
            }
            if ($e instanceof NotFoundHttpException) {
                $status = 404;
            }
            if ($status < 400 || $status > 599) {
                $status = 500;
            }

            $message = match (true) {
                $status === 401 => 'Token is missing or could not be decoded.',
                $status === 403 => 'You do not have permission to perform this action.',
                $status === 404 => 'Not found.',
                $status === 405 => 'Method not allowed.',
                $status === 422 => 'Invalid data.',
                $status === 429 => 'Too many requests. Please try again later.',
                $status >= 500 => 'An error occurred. Please try again.',
                default => 'Invalid request.',
            };

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        });
    })->create();
