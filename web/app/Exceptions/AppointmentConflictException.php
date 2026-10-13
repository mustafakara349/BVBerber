<?php

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class AppointmentConflictException extends RuntimeException
{
    public function __construct(
        string $message = 'Seçilen saat dolu veya başka bir müşteri tarafından yeni alındı. Lütfen farklı bir saat seçiniz.',
        int $code = Response::HTTP_CONFLICT,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
