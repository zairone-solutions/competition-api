<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PostVoter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $competition = $request->route('competition');
        if (auth()->user()->id == $competition->organizer_id || auth()->user()->participations()->where("competition_id",  $competition->id)->count()) {
            return response()->json(['error_type' => "authorization", "messages" => [
                "error" => "Only a voter can perform this action."
            ]]);
        }
        return $next($request);
    }
}
