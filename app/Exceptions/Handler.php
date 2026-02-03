<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Lista de exceções que não devem ser reportadas.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Campos sensíveis que não devem ser armazenados em sessões de erro.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // Registro adicional de callbacks pode ser feito aqui
    }

    public function render($request, Throwable $exception): Response
    {
        return parent::render($request, $exception);
    }
}
