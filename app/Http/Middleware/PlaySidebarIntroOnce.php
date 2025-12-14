<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PlaySidebarIntroOnce
{
    public function handle(Request $request, Closure $next)
    {
        // เล่นเฉพาะ user ที่ login แล้ว และยังไม่เคยเล่นใน session นี้
        if ($request->user() && !$request->session()->has('played_sidebar_intro')) {
            $request->session()->flash('play_sidebar_intro', true);
            $request->session()->put('played_sidebar_intro', true);
        }

        return $next($request);
    }
}
