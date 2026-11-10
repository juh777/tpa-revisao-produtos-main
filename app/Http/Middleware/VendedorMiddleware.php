<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendedorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se não tiver nenhum usuário logado, joga para o login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // TESTE TEMPORÁRIO: Se for cliente (carla), vai travar e dar o erro 403 na hora
        if (auth()->user()->tipo !== 'vendedor') {
            abort(403, 'Acesso negado. Seu tipo é: ' . auth()->user()->tipo);
        }

        return $next($request);
    }
}
