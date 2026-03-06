<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PaymentGatewayException extends Exception
{
    public function __construct(
        string $message,
        protected string $errorCode = 'payment_gateway_error',
        protected int $httpStatus = 422,
        protected array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function context(): array
    {
        return $this->context;
    }
}
