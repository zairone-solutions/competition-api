<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        // $this->renderable(function ($e, $request) {
        //     if ($request->is('api/*')) {
        //         if ($e instanceof ParseError) {
        //             return response()->json([
        //                 'error' => 'Parse Error',
        //                 'message' => 'There was an error parsing the request data.',
        //                 'code' => 400, // Adjust the code as needed (e.g., 422 for unprocessable entity)
        //             ], 400);
        //         }
        //         if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
        //             return response()->json([
        //                 'error_type' => 'authorization', 'messages' => 'Invalid token ability.'
        //             ], 401);
        //         }
        //     }
        //     return parent::render($request, $e);
        // });
    }
    public function render($request, $e)
    {
        // die(get_class($e));
        if ($request->is('api/*')) {
            if (get_class($e) == 'ParseError' || get_class($e) == 'ErrorException') {
                return response()->json([
                    'error_type' => 'server', 'messages' => ["error" => $e->getMessage()]
                ], 500);
            }
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'error_type' => 'validation', 'messages' => 'Url entity does not exist.'
                ], 404);
            }
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                return response()->json([
                    'error_type' => 'authorization', 'messages' => 'Invalid token ability.'
                ], 401);
            }
        }
        return parent::render($request, $e);
    }
}
