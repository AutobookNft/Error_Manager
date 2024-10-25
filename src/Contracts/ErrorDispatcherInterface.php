<?php

namespace Fabio\UltraErrorManager\Contracts;

use Throwable;

interface ErrorDispatcherInterface
{
    /**
     * Handles the exception and returns the details for the response.
     *
     * @param Throwable $exception
     * @return array|string|bool
     */
    public function handle(Throwable $exception): array|string|bool;

    /**
     * Map the exception to an error code.
     *
     * @param Throwable $exception
     * @return string
     */
    public function mapExceptionToErrorCode(Throwable $exception): string;

    /**
     * Log the error in the appropriate channels.
     *
     * @param string $errorCode
     * @param string $devMessage
     * @param Throwable $exception
     * @return void
     */
    public function logError(string $errorCode, string $devMessage, Throwable $exception): void;

    /**
     * Notify the DevTeam of critical errors.
     *
     * @param string $errorCode
     * @param string $devMessage
     * @param Throwable $exception
     * @return void
     */
    public function notifyDevTeam(string $errorCode, string $devMessage, Throwable $exception): void;
}
