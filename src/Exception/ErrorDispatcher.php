<?php

namespace Fabio\ErrorManager\Exception;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Fabio\ErrorManager\Mail\ErrorOccurredMailable;
use Fabio\ErrorManager\Contracts\ErrorDispatcherInterface;
use Fabio\UltraSecureUpload\ConfigManager;
use Throwable;

class ErrorDispatcher implements ErrorDispatcherInterface
{

    protected $routeChannel;
    protected $encodedLogParams;
    protected $configManager;

    /**
     * Handles the exception and returns the details for the response.
     *
     * @param Throwable $exception
     * @return array ['message' => string, 'errorCode' => string, 'state' => string, 'http_status_code' => int]
     */
    public function handle(Throwable $exception): array|string|bool
    {

        $this->configManager = app(ConfigManager::class);
        
        //Retrieve channel name for logging
        $this->routeChannel = $this->configManager->getRouteChannel();
        
        $this->encodedLogParams = json_encode([
            'Class' => 'ErrorDispatcher',
            'Method' => 'handle',
        ]);
        
        try {

            // Map the exception to an error code
            $errorCode = $this->mapExceptionToErrorCode($exception);
            // Retrieve error details from configuration
            $errorDetails = config('error_messages')[$errorCode] ?? null;
            // Retrieve the user message
            $userMessage = __('errors.' . $errorCode);
                        
            Log::channel($this->routeChannel)->info($this->encodedLogParams, [
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
            $this->logError($errorCode, $errorDetails['dev_message'], $exception);

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
                'emailSent' => $emailSent,
            ];

            Log::channel($this->routeChannel)->error($this->encodedLogParams,
            [
                'Type error' => 'Handled error',
                'ErrorArray' => $errorArray,
            ]);
           
            // Returns the Json with all the dispatcher data
            // This is the correct answer without errors
            return json_encode($errorArray);

        } catch (Throwable $e) {
            
            // Logs the unhandled error
            Log::channel($this->routeChannel)->error($this->encodedLogParams,
            [
                'Type error' => 'Unhandled error',
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
        
        $this->encodedLogParams = json_encode([
            'Class' => 'ErrorDispatcher',
            'Method' => 'mapExceptionToErrorCode',
        ]);
        
        if ($exception instanceof AuthenticationException) {
            Log::channel($this->routeChannel)->error($this->encodedLogParams,
            [
                'Type error' => 'Handled error',
                'Exception' => get_class($exception),
            ]);
            
            return 'AUTHENTICATION_ERROR';
        }

        if ($exception instanceof CustomException) {


            $stringCode = $exception->getStringCode() ?? 'UNEXPECTED_ERROR';

            Log::channel($this->routeChannel)->error($this->encodedLogParams,
            [
                'Type error' => 'Handled error',
                'Action' => 'CustomException',
                'Exception' => get_class($exception),
                'StringCode' => $stringCode,
            ]);

            return $stringCode;
        }

        // Logs the unhandled error
        Log::channel($this->routeChannel)->error($this->encodedLogParams,
        [
            'Type error' => 'Unhandled error',
            'Action' => 'CustomException',
            'Class Exception' => get_class($exception),
        ]);

        // If the flow reaches this far, there was an unexpected error.
        return 'UNEXPECTED_ERROR';
    }

    /**
     * Logga l'errore nei canali appropriati.
     *
     * @param string $errorCode
     * @param string $devMessage
     * @param Throwable $exception
     * @return void
     */
    public function logError(string $errorCode, string $devMessage, Throwable $exception): void
    {
        
        $this->encodedLogParams = json_encode([
            'Class' => 'ErrorDispatcher',
            'Method' => 'logError',
        ]);        
        
        Log::channel($this->routeChannel)->error($this->encodedLogParams,[
            'Type error' => 'Handled error',
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
     * Notifica il DevTeam in caso di errori critici.
     *
     * @param string $errorCode
     * @param string $devMessage
     * @param Throwable $exception
     * @return void
     */
    public function notifyDevTeam(string $errorCode, string $devMessage, Throwable $exception): void
    {

        $this->encodedLogParams = json_encode([
            'Class' => 'ErrorDispatcher',
            'Method' => 'notifyDevTeam',
        ]); 

        $user = Auth::user() ?? null;

        $params = [
            'subject' => 'Errore Critico Rilevato: ' . $errorCode,
            'devMessage' => $devMessage,
            'user_id' => $user->id ?? null,
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
        
        Log::channel($this->routeChannel)->error($this->encodedLogParams,
            [
            'Type error' => 'Handled error',
            'params' =>  $params 
        ]);

        if ($this->configManager->getRouteChannel()) {

            Mail::to($this->configManager->getDevTeamEmail())->send(new ErrorOccurredMailable($params));
        
        } else {
        
           // Simulate email sending if disabled
           Log::channel($this->routeChannel)->error($this->encodedLogParams,[
                'Message' => 'Email Sending Simulation: ' . json_encode($params),
           ]);

        }
    }
}



