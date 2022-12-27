<?php
declare(strict_types=1);

namespace Chs\Geoname\Util;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class Db
{
    /**
     * @var ?Connection $connection
     */
    private static ?Connection $connection = null;

    /**
     * Get the application database connection
     * @throws Exception
     */
    public static function getConnection(): Connection {
        if(self::$connection === null){
            $config = new Configuration();

            $connectionParams = [
                'user' => $_ENV['DATABASE_USER'],
                'password' => $_ENV['DATABASE_PASS'],
                'host' => $_ENV['DATABASE_HOST'],
                'dbname' => $_ENV['DATABASE_NAME'],
                'port' => $_ENV['DATABASE_PORT'],
                'charset' => 'utf8mb4',
                'driver' => 'pdo_mysql'
            ];

            self::$connection = DriverManager::getConnection($connectionParams, $config);
            self::$connection->executeQuery('SET NAMES utf8mb4;');
        }
        return self::$connection;
    }
}