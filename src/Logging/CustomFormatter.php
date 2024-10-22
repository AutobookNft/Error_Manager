<?php

// app/Logging/CustomFormatter.php
namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class CustomFormatter extends LineFormatter
{
    public function format(LogRecord $record): string
    {
        // Aggiorna il formato del timestamp con i millisecondi
        $datetime = $record->datetime->format('Y-m-d H:i:s.u');

        // Ricrea il LogRecord con il datetime aggiornato
        $record = new LogRecord(
            $record->datetime,
            $record->channel,
            $record->level,
            $record->message,
            $record->context,
            $record->extra,
            $record->formatted
        );

        // Chiamata al metodo parent::format() con il LogRecord aggiornato
        return parent::format($record);
    }
}

