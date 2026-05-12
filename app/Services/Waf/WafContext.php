<?php

namespace App\Services\Waf;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * WafContext - tudo que o engine precisa saber da requisicao para
 * inspecionar. Imutavel apos construcao.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 9.2
 */
final class WafContext
{
    /**
     * @param array<string,string>  $headers     Cabecalhos (chave em lower-case)
     * @param array<string,mixed>   $query       Parametros de query
     * @param array<string,mixed>|string $body    Corpo decodificado ou string
     * @param array<string,string>  $cookies     Cookies recebidos
     */
    public function __construct(
        public readonly string  $requestId,
        public readonly string  $ip,
        public readonly string  $method,
        public readonly ?string $routeName,
        public readonly string  $path,
        public readonly array   $headers,
        public readonly array   $query,
        public readonly mixed   $body,
        public readonly array   $cookies,
        public readonly ?int    $userId,
        public readonly ?string $userRole,
        public readonly ?string $userAgent,
        public readonly ?string $referrer,
        public readonly ?string $country = null,
        public readonly ?int    $asn = null,
        public readonly ?string $scope = 'default', // default|login|api|webhook|upload|admin
    ) {}

    /**
     * Constroi o contexto a partir do Request do Laravel.
     */
    public static function fromRequest(Request $request, ?string $scope = null): self
    {
        $requestId = $request->headers->get('X-Request-Id');
        if (empty($requestId)) {
            $requestId = (string) Str::uuid();
        }

        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            $headers[strtolower($key)] = is_array($values) ? implode(',', $values) : (string) $values;
        }

        $body = [];
        if ($request->isJson()) {
            $decoded = json_decode($request->getContent(), true);
            $body = is_array($decoded) ? $decoded : [];
        } else {
            $body = $request->request->all();
        }

        $user = $request->user();

        return new self(
            requestId: $requestId,
            ip:        (string) $request->ip(),
            method:    strtoupper($request->method()),
            routeName: optional($request->route())->getName(),
            path:      '/' . ltrim($request->path(), '/'),
            headers:   $headers,
            query:     $request->query->all(),
            body:      $body,
            cookies:   array_map(fn ($v) => is_array($v) ? implode(',', $v) : (string) $v, $request->cookies->all()),
            userId:    $user?->id,
            userRole:  method_exists($user, 'role_name') ? null : (string) ($user->role ?? null),
            userAgent: (string) $request->userAgent(),
            referrer:  $request->headers->get('referer'),
            country:   null,
            asn:       null,
            scope:     $scope ?? self::resolveScope($request),
        );
    }

    /**
     * Detecta heuristicamente o escopo da requisicao.
     */
    public static function resolveScope(Request $request): string
    {
        $path = '/' . ltrim($request->path(), '/');

        if (str_starts_with($path, '/login') || str_ends_with($path, '/login')) {
            return 'login';
        }
        if (str_starts_with($path, '/api/')) {
            return 'api';
        }
        if (str_contains($path, '/webhook')) {
            return 'webhook';
        }
        if (str_contains($path, '/admin/')) {
            return 'admin';
        }

        return 'default';
    }

    /**
     * Clone com country/asn enriquecidos apos GeoIp lookup.
     */
    public function withGeo(?string $country, ?int $asn): self
    {
        return new self(
            requestId: $this->requestId,
            ip:        $this->ip,
            method:    $this->method,
            routeName: $this->routeName,
            path:      $this->path,
            headers:   $this->headers,
            query:     $this->query,
            body:      $this->body,
            cookies:   $this->cookies,
            userId:    $this->userId,
            userRole:  $this->userRole,
            userAgent: $this->userAgent,
            referrer:  $this->referrer,
            country:   $country,
            asn:       $asn,
            scope:     $this->scope,
        );
    }

    /**
     * Snapshot leve para amostragem em WafEvent (pre-mascaramento).
     * Campos sao concatenados em string unica para matching de regex.
     */
    public function targetString(string $field): string
    {
        return match ($field) {
            'query'      => (string) json_encode($this->query, JSON_UNESCAPED_UNICODE),
            'body'       => is_array($this->body) ? (string) json_encode($this->body, JSON_UNESCAPED_UNICODE) : (string) $this->body,
            'headers'    => (string) json_encode($this->headers, JSON_UNESCAPED_UNICODE),
            'path'       => $this->path,
            'cookies'    => (string) json_encode($this->cookies, JSON_UNESCAPED_UNICODE),
            'user_agent' => (string) $this->userAgent,
            'referrer'   => (string) $this->referrer,
            'all'        => implode(' ', [
                $this->path,
                (string) json_encode($this->query, JSON_UNESCAPED_UNICODE),
                is_array($this->body) ? (string) json_encode($this->body, JSON_UNESCAPED_UNICODE) : (string) $this->body,
                (string) $this->userAgent,
            ]),
            default      => '',
        };
    }
}
