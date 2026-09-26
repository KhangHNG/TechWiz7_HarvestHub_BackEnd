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
                    'message' => 'Liên kết xác thực không hợp lệ hoặc đã hết hạn.',
                ], 403);
            }

            return response()->view('auth.email-verified', [
                'success' => false,
                'message' => 'Liên kết xác thực không hợp lệ hoặc đã hết hạn.',
            ], 403);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api', 'api/*') || $e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
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
                $status === 401 => 'Token bị thiếu hoặc không thể giải mã.',
                $status === 403 => 'Bạn không có quyền thực hiện thao tác này.',
                $status === 404 => 'Không tìm thấy.',
                $status === 405 => 'Phương thức không được hỗ trợ.',
                $status === 422 => 'Dữ liệu không hợp lệ.',
                $status === 429 => 'Bạn gửi quá nhiều yêu cầu. Vui lòng thử lại sau.',
                $status >= 500 => 'Đã xảy ra lỗi. Vui lòng thử lại.',
                default => 'Yêu cầu không hợp lệ.',
            };

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        });
    })->create();
