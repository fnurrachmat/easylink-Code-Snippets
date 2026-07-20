<?php

declare(strict_types=1);

namespace EasylinkIntegrator\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Class EasylinkException
 *
 * Custom exception class for all errors occurring within the Easylink SDK,
 * including authentication failures, HTTP communication issues, and invalid configuration.
 */
class EasylinkException extends RuntimeException
{
    /**
     * @var array|null The raw response payload from the API, if available.
     */
    private ?array $responsePayload;

    /**
     * @var int|null The HTTP status code, if available.
     */
    private ?int $statusCode;

    /**
     * EasylinkException constructor.
     *
     * @param string $message Exception message
     * @param int $code Error code
     * @param array|null $responsePayload Raw API response decoded as array
     * @param int|null $statusCode HTTP status code
     * @param Throwable|null $previous Previous exception for chaining
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?array $responsePayload = null,
        ?int $statusCode = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->responsePayload = $responsePayload;
        $this->statusCode = $statusCode;
    }

    /**
     * Get the raw response payload.
     *
     * @return array|null
     */
    public function getResponsePayload(): ?array
    {
        return $this->responsePayload;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
