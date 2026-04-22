<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ── Domain exceptions → 422 ────────────────────────────────────────
        $domainExceptions = [
            \App\Exceptions\InsufficientStockException::class,
            \App\Exceptions\SameWarehouseTransferException::class,
            \App\Exceptions\InactiveWarehouseException::class,
        ];

        foreach ($domainExceptions as $exceptionClass) {
            $exceptions->renderable(function ($e, $request) use ($exceptionClass) {
                if ($e instanceof $exceptionClass && $request->expectsJson()) {
                    return \App\Http\Responses\ApiResponse::error($e->getMessage(), 422);
                }
            });
        }

        // ── 404 → JSON ─────────────────────────────────────────────────────
        $exceptions->renderable(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return \App\Http\Responses\ApiResponse::notFound('Resource not found.');
            }
        });

        // ── Auth → 401 JSON ────────────────────────────────────────────────
        $exceptions->renderable(function (
            \Illuminate\Auth\AuthenticationException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return \App\Http\Responses\ApiResponse::unauthorized();
            }
        });

        // ── Validation → 422 JSON ──────────────────────────────────────────
        $exceptions->renderable(function (
            \Illuminate\Validation\ValidationException $e,
            $request
        ) {
            if ($request->expectsJson()) {
                return \App\Http\Responses\ApiResponse::validationError(
                    $e->errors(),
                    'Validation failed.'
                );
            }
        });

    })->create();
