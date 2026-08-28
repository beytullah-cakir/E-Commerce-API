<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // kullanıcı giriş yapmış mı ve admin mi kontrol ediyoruz
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Bu işlem için admin yetkisi gerekiyor.'], 403);
        }

        return $next($request);
    }
}
