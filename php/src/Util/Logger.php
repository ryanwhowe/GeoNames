<?php

namespace Chs\Geoname\Util;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonoLogger;

class Logger {

    protected static ?MonoLogger $logger = null;

    public static function getLogger() {
        if (self::$logger === null) {
            $logLevel = $_ENV['LOG_LEVEL'] ?? MonoLogger::DEBUG;
            self::$logger = new MonoLogger('geonames');
            self::$logger->pushHandler(new RotatingFileHandler('/tmp/logs/geonames.log', 60, $logLevel));
        }
        return self::$logger;
    }
}