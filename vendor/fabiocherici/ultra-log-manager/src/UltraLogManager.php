<?php

namespace Fabio\UltraLogManager;

use Fabio\PerfectConfigManager\ConfigManager;
use Illuminate\Support\Facades\Log;

use Throwable;

class UltraLogManager
{
    protected static string $routeChannel;
    protected static string $encodedLogParams;

    /**
     * Initialize log channel using ConfigManager.
     *
     * @return void
     */
    private static function initialize(): void
    {
        try {
            // Retrieve the log channel from the config using ConfigManager
            self::$routeChannel = ConfigManager::getConfig('log_channel', 'error_manager');
        } catch (Throwable $e) {
            // Fall back to a default channel if ConfigManager fails
            self::$routeChannel = 'stack';
        }
    }

    /**
     * Set log parameters.
     *
     * @param string $class
     * @param string $method
     * @return void
     */
    private static function setLogParams(string $class, string $method): void
    {
        self::$encodedLogParams = json_encode([
            'Class' => $class,
            'Method' => $method,
        ]);
    }

    /**
     * Log a message.
     *
     * @param string $level The log level (info, error, warning, etc.)
     * @param string $action The action being logged
     * @param string $message The message to log
     * @param string|null $class The class from which the log is generated
     * @param string|null $method The method from which the log is generated
     * @return void
     */
    public static function log(string $level, string $action, string $message, string $class = null, string $method = null): void
    {
        // Initialize log channel
        self::initialize();

        // If class and method are not provided, get the calling context
        if ($class === null || $method === null) {
            [$callerClass, $callerMethod] = self::getCallerContext();
            $class = $class ?? $callerClass;
            $method = $method ?? $callerMethod;
        }

        // Set log parameters
        self::setLogParams($class, $method);

        // Create log with specified channel or fall back to 'stack'
        try {
            Log::channel(self::$routeChannel)->{$level}(self::$encodedLogParams, [
                'Type' => $action,
                'Message' => $message,
            ]);
        } catch (Throwable $e) {
            // If the channel is invalid, use Laravel's default channel
            Log::channel('stack')->{$level}(self::$encodedLogParams, [
                'Type' => $action,
                'Message' => $message,
                'FallbackChannel' => true,  // Indication that fallback channel was used
            ]);
        }
    }

    /**
     * Get the calling class and method.
     *
     * @return array
     */
    private static function getCallerContext(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        // Index 2 should represent the caller of the 'log' method
        $caller = $backtrace[2] ?? null;

        return [
            $caller['class'] ?? 'UnknownClass',
            $caller['function'] ?? 'UnknownMethod',
        ];
    }
}
