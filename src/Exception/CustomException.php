<?php

namespace Fabio\UltraErrorManager\Exception;

use Exception;

class CustomException extends Exception
{
    protected string $stringCode;
    protected string $routeChannel;
    protected string $encodedLogParams;

    /**
     * Constructor of the CustomException.
     *
     * @param string $stringCode Custom error code.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(string $stringCode, \Throwable $previous = null)
    {
        $this->stringCode = $stringCode;

        parent::__construct('Custom message: ' . $stringCode, 1, $previous);
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
