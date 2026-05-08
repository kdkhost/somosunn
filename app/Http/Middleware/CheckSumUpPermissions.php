<?php

namespace App\Http\Middleware;

use App\Services\SumUpService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSumUpPermissions
{
    protected $sumupService;

    public function __construct(SumUpService $sumupService)
    {
        $this->sumupService = $sumupService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$parameters): Response
    {
        // Verificar se o SumUp está habilitado
        if (!$this->sumupService->isEnabled()) {
            return response()->json([
                'error' => 'SumUp não está habilitado.',
                'fallback_available' => $this->sumupService->shouldFallbackToMercadoPago()
            ], 403);
        }

        // Verificar parâmetros opcionais
        $userType = $request->input('user_type') ?? $this->getUserType($request);
        $productType = $request->input('product_type');
        $amount = $request->input('amount');

        // Verificar permissões por tipo de usuário
        if ($userType && !$this->sumupService->isAllowedForUser($userType)) {
            return response()->json([
                'error' => 'SumUp não está disponível para este tipo de usuário.',
                'user_type' => $userType
            ], 403);
        }

        // Verificar permissões por tipo de produto
        if ($productType && !$this->sumupService->isAllowedForProduct($productType)) {
            return response()->json([
                'error' => 'SumUp não está disponível para este tipo de produto.',
                'product_type' => $productType
            ], 403);
        }

        // Verificar limites de valor
        if ($amount && !$this->sumupService->isAmountAllowed($amount)) {
            return response()->json([
                'error' => 'Valor fora dos limites permitidos para SumUp.',
                'amount' => $amount
            ], 403);
        }

        return $next($request);
    }

    /**
     * Determina o tipo de usuário baseado na requisição
     */
    protected function getUserType(Request $request): ?string
    {
        $user = $request->user();
        
        if (!$user) {
            return 'member';
        }

        // Lógica para determinar o tipo de usuário
        // Adapte conforme sua estrutura de usuários
        if ($user->isAdmin()) {
            return 'member'; // Admin pode usar como membro
        }

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('instructor')) {
                return 'instructor';
            }

            if ($user->hasRole('seller')) {
                return 'seller';
            }

            if ($user->hasRole('mentor')) {
                return 'mentor';
            }
        }

        return 'member';
    }
}
