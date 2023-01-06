<?php

namespace Chs\Geoname\Command;

use Chs\Geoname\Entity\IncrementalUpdate;
use Chs\Geoname\Util\Db;
use Chs\Geoname\Util\Logger;
use Chs\Geoname\Util\MessageQueue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeQueueUpdatesCommand extends BaseCommand {

    protected static $defaultName = 'geonames:consume-queue-updates';

    /**
     * @var AMQPChannel
     */
    protected AMQPChannel $connection;

    public function __construct(string $name = null) {
        parent::__construct($name);
        $this->log = Logger::getLogger();
    }

    protected function configure() {
        $this
            ->setDescription('Consume queued updates and persist them to the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output);
        $this->log->pushProcessor(function ($entry) {
            $entry['extra']['class'] = self::class;
            return $entry;
        });
        $this->log->notice("Process Starting");

        $this->connection = MessageQueue::getChannel();

        $this->rabbitmqConsumer();

        register_shutdown_function('Chs\Geoname\Util\MessageQueue::cleanupConnection', $this->connection);
        $completed = false;

        while (true) {
            try {
                $this->connection = MessageQueue::getChannel();

                $completed = true; // break out of the main loop we have completed the functional portion of the loop
            } catch (AMQPRuntimeException $e) {
                echo $e->getMessage() . PHP_EOL;
                MessageQueue::cleanupConnection($this->connection);
                usleep(MessageQueue::CONNECTION_RECONNECT_WAIT);
            } catch (\RuntimeException $e) {
                echo "Runtime exception " . PHP_EOL;
                MessageQueue::cleanupConnection($this->connection);
                usleep(MessageQueue::CONNECTION_RECONNECT_WAIT);
            } catch (\ErrorException $e) {
                echo "Error exception " . PHP_EOL;
                MessageQueue::cleanupConnection($this->connection);
                usleep(MessageQueue::CONNECTION_RECONNECT_WAIT);
            }
            if($completed) break;
        }


        $this->log->notice("Process Completed");
        return self::SUCCESS;
    }

    public function rabbitmqConsumer(){
        $this->connection->queue_declare(MessageQueue::QUEUE_UPDATE, false, true, false, false);
        $this->connection->basic_qos(null, 1, null);
        $this->connection->basic_consume(
            MessageQueue::QUEUE_UPDATE,
            '',
            false,
            false,
            false,
            false,
            '\Chs\Geoname\Command\ConsumeQueueUpdatesCommand::processFunction'
        );
        while(count($this->connection->callbacks)){
            $this->connection->wait();
        }

    }

    public static function processFunction(AMQPMessage $msg) {
        $incrementalUpdate = new IncrementalUpdate();
        $incrementalUpdate->unserialize($msg->getBody());

        if($incrementalUpdate->isDelete()){
            Db::getConnection()->delete('geoname', ['geonameid' => $incrementalUpdate->getData()->getGeonameid()]);
        } elseif ($incrementalUpdate->isUpdate()){
            Db::getConnection()->update('geoname',
                [
                    'name' => $incrementalUpdate->getData()->getName(),
                    'asciiname' => $incrementalUpdate->getData()->getAsciiname(),
                    'alternatenames' => $incrementalUpdate->getData()->getAlternatenames(),
                    'latitude' => $incrementalUpdate->getData()->getLatitude(),
                    'longitude' => $incrementalUpdate->getData()->getLongitude(),
                    'fclass' => $incrementalUpdate->getData()->getFclass(),
                    'fcode' => $incrementalUpdate->getData()->getFcode(),
                    'country' => $incrementalUpdate->getData()->getCountry(),
                    'cc2' => $incrementalUpdate->getData()->getCc2(),
                    'admin1' => $incrementalUpdate->getData()->getAdmin1(),
                    'admin2' => $incrementalUpdate->getData()->getAdmin2(),
                    'admin3' => $incrementalUpdate->getData()->getAdmin3(),
                    'admin4' => $incrementalUpdate->getData()->getAdmin4(),
                    'population' => $incrementalUpdate->getData()->getPopulation(),
                    'elevation' => $incrementalUpdate->getData()->getElevation(),
                    'gtopo30' => $incrementalUpdate->getData()->getGtopo30(),
                    'timezone' => $incrementalUpdate->getData()->getTimezone(),
                    'moddate' => $incrementalUpdate->getData()->getModdate(),
                ],
                ['geonameid' => $incrementalUpdate->getData()->getGeonameid()]);
        }

        $msg->ack(); // mark the message delivered and completed
    }

}