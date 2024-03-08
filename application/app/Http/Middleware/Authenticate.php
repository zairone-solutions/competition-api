<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (in_array('api', $request->route()->getAction('middleware'))) {
            abort(response()->json(['error_type' => "authorization", "message" => [
                "error" => "Please provide a valid token."
            ]]));
        }
        return route('login');
    }
}
