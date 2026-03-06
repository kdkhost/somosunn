<?php

namespace App\Http\Middleware;

use App\Models\AffiliateApiSandboxRequest;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureAffiliateSandboxApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->deny('Autenticação necessária para acessar o sandbox da API.', 401);
        }

        if (!$this->hasSandboxRequestsTable()) {
            return $this->deny('O sandbox da API ainda não está disponível neste ambiente. Rode as migrations antes de testar.', 503);
        }

        $approval = AffiliateApiSandboxRequest::query()
            ->approved()
            ->where('user_id', $user->id)
            ->latest('reviewed_at')
            ->latest('id')
            ->first();

        if (!$approval) {
            return $this->deny('Sandbox não liberado para este afiliado. Solicite acesso no painel informando motivo, IP e domínio.', 403);
        }

        if (!$approval->matchesRequest($request)) {
            return $this->deny('Acesso negado ao sandbox. O domínio ou IP desta chamada não corresponde ao ticket aprovado.', 403);
        }

        $request->attributes->set('affiliateSandboxApproval', $approval);

        return $next($request);
    }

    private function hasSandboxRequestsTable(): bool
    {
        try {
            return Schema::hasTable('affiliate_api_sandbox_requests');
        } catch (\Throwable) {
            return false;
        }
    }

    private function deny(string $message, int $status): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'sandbox_access' => false,
        ], $status);
    }
}
