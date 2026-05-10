<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSuperadminToLegacy
{
    /**
     * O superadmin tem acesso a AMBOS os paineis:
     * - Painel legado (/admin) para administracao geral
     * - Painel novo (/painel) para funcoes de vendedor, instrutor, eventos, etc.
     *
     * Este middleware nao bloqueia mais o acesso do superadmin ao painel novo.
     * O menu da navbar direciona o superadmin para /admin por padrao,
     * mas ele pode acessar /painel normalmente para suas funcoes de vendedor/instrutor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
