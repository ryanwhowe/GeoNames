<?php

namespace Chs\Geoname\Command;

use Chs\Geoname\Entity\IncrementalUpdate;
use Chs\Geoname\Exception\GeoNameIncrementalFileNotFound;
use Chs\Geoname\Util\Db;
use Chs\Geoname\Util\Logger;
use Chs\Geoname\Util\MessageQueue;
use Doctrine\DBAL\Exception;
use Chs\Geoname\Entity\GeoName;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ParseUpdateFilesCommand extends BaseCommand {

    protected static $defaultName = 'geonames:parse-update-files';

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
            ->setDescription('Parse any unprocessed update files and add them to the update queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output);
        $this->log->pushProcessor(function ($entry) {
            $entry['extra']['class'] = self::class;
            return $entry;
        });
        $this->log->notice("Process Starting");

        $this->connection = MessageQueue::getChannel();

        register_shutdown_function('Chs\Geoname\Util\MessageQueue::cleanupConnection', $this->connection);
        $completed = false;
        while (true) {
            try {
                $this->connection = MessageQueue::getChannel();

                $incremental_files = $this->getIncrementalFileData();
                $this->log->info('Processing ' . count($incremental_files) . ' files');

                foreach ($incremental_files as $incremental_file) {
                    $context_log = [
                        "filename" => $incremental_file['filename'], 'id' => $incremental_file['id']
                    ];
                    $this->log->info('Processing file', $context_log);
                    $this->log->debug('Updating file record', $context_log);

                    try {
                        $updates = $this->parseFile($incremental_file['filename']);
                        $this->log->debug(count($updates) . ' update records read');
                        $count = $this->sendUpdates($updates);
                        $this->log->debug($count . ' messages sent');
                        $this->updateIncrementalFile($incremental_file['id']);
                    } catch (\Exception $e) {
                        $this->log->critical('unable to update file record (id:' . $context_log['id'] . '): ' . $e->getMessage(), $e->getTrace());
                    }

                    $this->log->debug('File record updated', $context_log);
                }
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

    /**
     * @return array[]
     * @throws Exception
     */
    protected function getIncrementalFileData(): array {
        $stmt = Db::getConnection()->prepare(<<<SQL
SELECT id, filename, is_processed, processed_date FROM incremental_files WHERE is_processed = 0 ORDER BY filename;
SQL
        );

        $result = $stmt->executeQuery();
        return $result->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    protected function updateIncrementalFile(int $id): int|string {
        return Db::getConnection()->executeStatement(<<<SQL
UPDATE incremental_files SET processed_date = NOW(), is_processed = 1 WHERE id = ?
SQL
, [$id]
        );
    }

    /**
     * @param IncrementalUpdate[] $updates
     * @return int
     * @throws \Exception
     */
    private function sendUpdates(array $updates): int {
        $this->connection->queue_declare(MessageQueue::QUEUE_UPDATE, false, true, false, false);
        $counter = 0;
        foreach ($updates as $update) {
            $msg = MessageQueue::makePersistentMessage($update->serialize());
            $this->connection->basic_publish($msg, '', MessageQueue::QUEUE_UPDATE);
            $counter++;
        }
        return $counter;
    }

    /**
     * @throws GeoNameIncrementalFileNotFound
     */
    private function parseFile(string $filename): array {
        $updates = [];

        if(file_exists($filename)){
            $file = new \SplFileObject($filename, 'r');
            while(!$file->eof()){
                $line = $file->fgets();
                $parts = explode("\t", $line);

                if (0 === count($parts)) continue; // each file as a 0 length line at the end ¯\_(ツ)_/¯

                $parts = array_map('trim', $parts);
                $geoname = new GeoName();
                $type = IncrementalUpdate::TYPE_DELETE;
                $geoname->setGeonameid((int)$parts[0]);

                if (count($parts) > 3) {
                    $type = IncrementalUpdate::TYPE_UPDATE;
                    $geoname->setName($parts[1])
                        ->setAsciiname($parts[2])
                        ->setAlternatenames($parts[3])
                        ->setLatitude((float)$parts[4])
                        ->setLongitude((float)$parts[5])
                        ->setFclass($parts[6])
                        ->setFcode($parts[7])
                        ->setCountry($parts[8])
                        ->setCc2($parts[9])
                        ->setAdmin1($parts[10])
                        ->setAdmin2($parts[11])
                        ->setAdmin3($parts[12])
                        ->setAdmin4($parts[13])
                        ->setPopulation((int)$parts[14])
                        ->setElevation((int)$parts[15])
                        ->setGtopo30((int)$parts[16])
                        ->setTimezone($parts[17])
                        ->setModdate($parts[18]);
                }

                $update = new IncrementalUpdate();
                $update->setType($type)->setData($geoname);
                $updates[] = $update;
            }
            $file = null; // close the file
        } else {
            throw new GeoNameIncrementalFileNotFound($filename);
        }

        return $updates;
    }
}