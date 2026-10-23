<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorTrackingService
{
    /**
     * Sensitive input parameters to strip from logs.
     */
    protected array $hiddenParameters = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'secret',
        'authorization',
        'credit_card',
        'card_number',
        'cvv',
        'cvc',
    ];

    /**
     * Generate an 8-character unique collision-safe tracking reference ID.
     */
    public function generateReferenceId(): string
    {
        return 'REF-' . strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Log the exception safely with sanitized request context and reference ID.
     */
    public function logException(Throwable $exception, ?Request $request = null, ?string $referenceId = null): string
    {
        $ref = $referenceId ?: $this->generateReferenceId();

        try {
            $context = [
                'reference_id' => $ref,
                'exception_class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];

            if ($request) {
                $context['url'] = $request->fullUrl();
                $context['method'] = $request->method();
                $context['ip'] = $request->ip();
                $context['user_agent'] = Str::limit($request->userAgent(), 150);

                if ($user = $request->user()) {
                    $context['user_id'] = $user->id;
                    $context['user_role'] = $user->role?->slug ?? null;
                    $context['user_email'] = $user->email ?? null;
                }

                $context['input'] = $this->sanitizeInput($request->except($this->hiddenParameters));
            }

            // Determine log level
            $statusCode = $this->resolveStatusCode($exception);
            if ($statusCode >= 500) {
                Log::error("[{$ref}] {$exception->getMessage()}", $context);
            } elseif ($statusCode === 403 || $statusCode === 429) {
                Log::warning("[{$ref}] {$exception->getMessage()}", $context);
            } else {
                Log::info("[{$ref}] {$exception->getMessage()}", $context);
            }

            // Record to AuditLog database table with complete error context (fail-safe)
            try {
                \App\Models\AuditLog::record(
                    action: 'system.exception',
                    modelType: 'exception',
                    modelId: 0,
                    oldValues: null,
                    newValues: [
                        'reference_id' => $ref,
                        'exception_class' => get_class($exception),
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'status_code' => $statusCode,
                        'url' => $request?->fullUrl(),
                        'method' => $request?->method(),
                        'trace' => array_slice(explode("\n", $exception->getTraceAsString()), 0, 20),
                        'input' => $context['input'] ?? null,
                    ],
                    userId: $request?->user()?->id,
                    status: $statusCode >= 500 ? 'error' : ($statusCode === 403 ? 'warning' : 'failed'),
                    details: "[{$ref}] ({$statusCode}) {$exception->getMessage()}"
                );
            } catch (Throwable $auditError) {
                error_log("Failed to write audit log for exception [{$ref}]: " . $auditError->getMessage());
            }
        } catch (Throwable $loggingError) {
            // Failsafe: Never let logging error interrupt user error presentation
            error_log("Failed to log error [{$ref}]: " . $loggingError->getMessage());
        }

        return $ref;
    }

    /**
     * Determine if the exception is a server-side 5xx or unhandled error.
     */
    public function isServerError(Throwable $exception): bool
    {
        return $this->resolveStatusCode($exception) >= 500;
    }

    /**
     * Resolve the HTTP status code from any Throwable.
     */
    public function resolveStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return 401;
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return 403;
        }

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return 404;
        }

        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return 419;
        }

        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return 429;
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return 422;
        }

        return 500;
    }

    /**
     * Sanitize input array recursively.
     */
    protected function sanitizeInput(array $input): array
    {
        $sanitized = [];
        foreach ($input as $key => $value) {
            if (in_array(strtolower((string) $key), $this->hiddenParameters, true)) {
                $sanitized[$key] = '***FILTERED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } elseif (is_string($value) && strlen($value) > 255) {
                $sanitized[$key] = Str::limit($value, 255);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
