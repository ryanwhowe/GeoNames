<?php declare(strict_types=1);

namespace Chs\Geoname\Util;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;

class MessageQueue{

    const QUEUE_UPDATE = 'Geoname_Update';

    const CONNECTION_RECONNECT_WAIT = 1000000;

    private static ?AMQPChannel $channel = null;

    /**
     * @return AMQPChannel
     * @throws \Exception
     */
    public static function getChannel(): AMQPChannel{
        if(self::$channel === null){
            $connected = false;
            while(!$connected){
                try{
                    $rabbit_connection = new AMQPStreamConnection(
                        $_ENV['RABBITRQ_HOST'],
                        $_ENV['RABBITRQ_PORT'],
                        $_ENV['RABBITRQ_USER'],
                        $_ENV['RABBITRQ_PASS']
                    );
                    self::$channel = $rabbit_connection->channel();
                    $connected = true;
                } catch (AMQPIOException|AMQPConnectionClosedException $e){
                    echo "RabbitMQ not available ". PHP_EOL;
                    usleep(self::CONNECTION_RECONNECT_WAIT);
                }
            }
        }
        return self::$channel;
    }

    /**
     * Generate an AMQPMessage.  This will automatically json_encode the array that is given to it
     * @param string|array $data array The message array to be json_encoded and attach to the AMQPMessage object
     * @return AMQPMessage
     */
    public static function makePersistentMessage(string|array $data): AMQPMessage{
        $send = (is_array($data))? json_encode($data) : $data;
        return new AMQPMessage($send, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
    }

    /**
     * @param ?AMQPChannel $connection
     */
    public static function cleanupConnection(?AMQPChannel $connection) {
        try{
            if($connection !== null){
                self::$channel = null;
                $connection->close();
            }
        } catch (\ErrorException $e){
            // just ignore the exception in case the connection was already closed
        }
    }
}
