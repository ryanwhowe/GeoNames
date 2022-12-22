<?php

namespace Chs\Message\Command;

use Chs\Message\Util\Db;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoadAllCountriesCommand extends BaseCommand {

    const GEONAMES_BASE_URL = 'https://download.geonames.org/export/dump/';
    const DESTINATION_BASE_DIR = '/tmp/data';
    const HTTP_OK = 200;
    const BULK_INSERT = 900;

    protected static $defaultName = 'geonames:load-allcountries';

    protected function configure() {
        $this
            ->setDescription('Load the All Countries datafile')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Check if available, do not download')
            ->setHelp(<<<TXT

Load the All Countries Data file

WARNING: This TAKES A LONG TIME!

TXT
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output);
        $dryrun = $input->getOption('dry-run');

        if($dryrun){
            $connection = null;
        } else {
            $connection = Db::getConnection();
            $this->log("Truncating the geonames table");
            $connection->executeQuery("TRUNCATE TABLE geoname;");
        }

        $this->log("Gathering file data");
        $lines = $this->getLineCount("/tmp/data/allCountries.txt");
        $this->log("loading ${lines} records");
        $pg = $this->io->createProgressBar($lines);
        $handle = fopen("/tmp/data/allCountries.txt", "r");

        $sql = <<<SQL
INSERT INTO geoname (geonameid, name, asciiname, alternatenames, latitude, longitude, fclass, fcode, country, cc2, admin1, admin2, admin3, admin4, population, elevation, gtopo30, timezone, moddate) 
VALUES 
SQL;
        $params = $inserts = [];
        $counter = 1;

        if($handle){
            $pg->start($lines);
            while (($line = fgets($handle)) !== false){
                $parts = explode("\t", $line);
                $parts = array_map('trim', $parts);
                if($dryrun) {
                    $this->dataDump($parts); die();
                } else {

                    $inserts[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                             $params[] = $parts[0];
                             $params[] = $parts[1];
                             $params[] = $parts[2];
                             $params[] = mb_substr($parts[3],0,10000);
                             $params[] = $parts[4];
                             $params[] = $parts[5];
                             $params[] = $parts[6];
                             $params[] = $parts[7];
                             $params[] = $parts[8];
                             $params[] =  mb_substr($parts[9],0,60);
                             $params[] = $parts[10];
                             $params[] = $parts[11];
                             $params[] = $parts[12];
                             $params[] = $parts[13];
                             $params[] = $parts[14];
                             $params[] = $this->stringToNumber($parts[15]);
                             $params[] = $parts[16];
                             $params[] = $parts[17];
                             $params[] = $parts[18];

                    try {
                        if(($counter % self::BULK_INSERT) === 0) {
                            $connection->executeQuery($sql . implode(',', $inserts), $params);
                            $params = [];
                            $inserts = [];
                        }
                    } catch (\Exception $e){
                        dump($e);
                        $this->dataDump($parts);
                        return self::FAILURE;
                    }
                    $counter++;
                }
                $pg->advance();
            }
        }

        fclose($handle);

        $pg->finish();

        return self::SUCCESS;

    }

    protected function getLineCount($file): int {
        $file = new \SplFileObject($file, "r");
        $file->seek(PHP_INT_MAX);
        $result = $file->key() + 1;
        unset($file);
        return $result;
    }

    protected function stringToNumber($data){
        if($data === '') return 0;
        return (int)$data;
    }

    protected function dataDump(array $data){
        dump([
            ':geonameid' => $data[0],
            ':name' => $data[1],
            ':asciiname' => $data[2],
            ':alternatenames' => mb_substr($data[3],0,10000),
            ':latitude' => $data[4],
            ':longitude' => $data[5],
            ':fclass' => $data[6],
            ':fcode' => $data[7],
            ':country' => $data[8],
            ':cc2' => mb_substr($data[9],0,60),
            ':admin1' => $data[10],
            ':admin2' => $data[11],
            ':admin3' => $data[12],
            ':admin4' => $data[13],
            ':population' => $data[14],
            ':elevation' => $this->stringToNumber($data[15]),
            ':gtopo30' => $data[16],
            ':timezone' => $data[17],
            ':moddate' => $data[18],
        ]);
    }

}