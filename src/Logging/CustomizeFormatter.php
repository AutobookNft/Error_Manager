<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;

class CustomizeFormatter
{
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                $handler->setFormatter(new CustomFormatter());
            }
        }
    }
}
