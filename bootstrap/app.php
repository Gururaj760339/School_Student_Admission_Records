<?php

use App\Exceptions\GoogleSheetsException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Safety net: any uncaught Google Sheets problem renders a safe,
        // credential-free error page instead of a stack trace.
        $exceptions->render(function (GoogleSheetsException $e, Request $request) {
            $status = $e->isSetupError() ? 500 : 503;

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $status);
            }

            return response()->view('errors.google-sheets', [
                'message' => $e->getMessage(),
                'isSetupError' => $e->isSetupError(),
            ], $status);
        });

        // Only the sanitised message is logged; never student data, request
        // payloads, or credentials.
        $exceptions->report(function (GoogleSheetsException $e): bool {
            Log::error('Google Sheets error: '.$e->getMessage());

            return false;
        });
    })->create();
