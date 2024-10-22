<?php

namespace Fabio\ErrorManager\Exception;

use Exception;
use Fabio\UltraSecureUpload\ConfigManager;
use Illuminate\Support\Facades\Log;

class CustomException extends Exception
{
    protected string $stringCode;
    protected string $routeChannel;
    protected string $encodedLogParams;
    protected $configManager;

    /**
     * Constructor of the CustomException.
     *
     * @param string $stringCode Custom error code.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(string $stringCode, \Throwable $previous = null)
    {
        
        $this->stringCode = $stringCode;
        
        $this->configManager = app(ConfigManager::class);
        
        // Retrieve channel name for logging
        $this->routeChannel = $this->configManager->getRouteChannel();
        
        $this->encodedLogParams = json_encode([
            'Class' => 'CustomException',
            'Method' => '__construct',
        ]);
                
        Log::channel($this->routeChannel)->error($this->encodedLogParams,
        [
            'Type error' => 'Handled error',
            'StringCode' => $stringCode,
        ]);

        parent::__construct( 'Custom message: '.$stringCode, 1, $previous); // Empty message or code 0
    }

    /**
     * Gets the custom error code.
     *
     * @return string
     */
    public function getStringCode(): string
    {
        return $this->stringCode;
    }
}
