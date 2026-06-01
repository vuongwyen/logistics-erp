<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Bắt lỗi bảng không tồn tại (SQLSTATE 42S02) — xảy ra khi
         * đang nghiệm thu Restore hoặc DB chưa được khởi tạo đầy đủ.
         * Hiển thị trang thân thiện thay vì trang lỗi mặc định Laravel.
         */
        $exceptions->render(function (QueryException $e) {
            if ($e->getCode() === '42S02') {
                return response()->view('errors.db-missing', [], 503);
            }
        });
    })->create();
