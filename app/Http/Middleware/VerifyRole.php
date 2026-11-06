<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure  $next
     * @param  string  $roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        // Jika belum login
        if (!$request->session()->has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = $request->session()->get('user');
        $allowedRoles = explode(',', $roles);

        // Cek apakah role user diizinkan
        foreach ($allowedRoles as $role) {
            if (strtolower($user['role']) === strtolower($role)) {
                return $next($request);
            }
        }

        // Role tidak sesuai
        return redirect()->route('welcome')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}