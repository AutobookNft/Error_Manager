<?php

namespace Fabio\ErrorManager\Exception;

use Fabio\PerfectConfigManager\ConfigManager;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Fabio\ErrorManager\Mail\ErrorOccurredMailable;
use Fabio\ErrorManager\Contracts\ErrorDispatcherInterface;
use Fabio\UltraLogManager\Facades\UltraLog;
use Throwable;

class ErrorDispatcher implements ErrorDispatcherInterface
{
    protected $encodedLogParams;
    protected $configManager;

    public function __construct()
    {
        // Initialization is now handled by UltraLog
    }

    /**
     * Handles the exception and returns the details for the response.
     *
     * @param Throwable $exception
     * @return array|string|bool
     */
    public function handle(Throwable $exception): array|string|bool
    {
        UltraLog::log('error', 'Start handling the exception');

        try {
            // Map the exception to an error code
            $errorCode = $this->mapExceptionToErrorCode($exception);
            // Retrieve error details from configuration
            $errorDetails = config('error_messages')[$errorCode] ?? null;
            // Retrieve the user message
            $userMessage = __('errors.' . $errorCode);

            UltraLog::log('info', 'Error details retrieved', [
                'ErrorCode' => $errorCode,
                'ErrorDetails' => $errorDetails,
                'UserMessage' => $userMessage,
            ]);

            if (!$errorDetails) {
                // Handle the undefined error case
                $errorDetails = [
                    'type' => 'critical',
                    'dev_message' => 'Undefined error: ' . $errorCode,
                    'userMessage' => __('UNEXPECTED_ERROR'),
                    'http_status_code' => 500,
                ];
            }

            // Error logging
            UltraLog::log('error', 'Logging the error', ['ErrorCode' => $errorCode]);

            // If the error is critical, notify the DevTeam
            if (strpos($errorDetails['type'], 'critical') !== false) {
                $this->notifyDevTeam($errorCode, $errorDetails['dev_message'], $exception);
                $emailSent = true;
            }

            $errorArray = [
                'userMessage' => $userMessage,
                'error' => $errorCode,
                'type' => $errorDetails['type'],
                'blocking' => $errorDetails['blocking'] ?? 'not',
                'http_status_code' => $errorDetails['http_status_code'],
                'devTeam_email_need' => $errorDetails['devTeam_email_need'] ?? false,
                'emailSent' => $emailSent ?? false,
            ];

            UltraLog::log('error', 'Error handled successfully', [
                'ErrorArray' => $errorArray,
            ]);

            // Returns the Json with all the dispatcher data
            return json_encode($errorArray);
        } catch (Throwable $e) {
            // Logs the unhandled error
            UltraLog::log('error', 'Unhandled error occurred', [
                'Error' => $e,
            ]);
            return false;
        }
    }

    /**
     * The function Maps the exception to an error code.
     *
     * @param Throwable $exception
     * @return string
     */
    public function mapExceptionToErrorCode(Throwable $exception): string
    {
        if ($exception instanceof AuthenticationException) {
            UltraLog::log('error', 'Authentication error handled', [
                'Exception' => get_class($exception),
            ]);
            return 'AUTHENTICATION_ERROR';
        }

        if ($exception instanceof CustomException) {
            $stringCode = $exception->getStringCode() ?? 'UNEXPECTED_ERROR';

            UltraLog::log('error', 'Custom exception handled', [
                'Action' => 'CustomException',
                'Exception' => get_class($exception),
                'StringCode' => $stringCode,
            ]);
            return $stringCode;
        }

        // Logs the unhandled error
        UltraLog::log('error', 'Unhandled exception occurred', [
            'Action' => 'CustomException',
            'Class Exception' => get_class($exception),
        ]);

        // If the flow reaches this far, there was an unexpected error.
        return 'UNEXPECTED_ERROR';
    }

    /**
     * Log the error in the appropriate channels.
     *
     * @param string $errorCode
     * @param string $devMessage
     * @param Throwable $exception
     * @return void
     */
    public function logError(string $errorCode, string $devMessage, Throwable $exception): void
    {
        UltraLog::log('error', 'Error occurred', [
            'Action' => 'Error occurred',
            'ErrorCode' => $errorCode,
            'devMessage' => $devMessage,
            'Exception' => get_class($exception),
            'File' => $exception->getFile(),
            'Line' => $exception->getLine(),
            'User' => Auth::user()->id ?? 'Guest',
            'Timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Notifies the DevTeam in case of critical errors.
     *
     * @param string $errorCode
     * @param string $devMessage
     * @param Throwable $exception
     * @return void
     */
    public function notifyDevTeam(string $errorCode, string $devMessage, Throwable $exception): void
    {
        
        $user = Auth::user() ?? null;

        $params = [
            'subject' => 'Critical Error Detected: ' . $errorCode,
            'devMessage' => $devMessage,
            'user_id' => $user->id ?? null,
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        UltraLog::log('error', 'Notifying DevTeam of critical error', [
            'params' => $params,
        ]);

        if ($this->configManager->getRouteChannel()) {
            Mail::to(ConfigManager::getConfig('devteam_email'))->send(new ErrorOccurredMailable($params));
        } else {
            // Simulate email sending if disabled
            UltraLog::log('error', 'Email Sending Simulation', [
                'Message' => 'Email Sending Simulation: ' . json_encode($params),
            ]);
        }
    }

}
