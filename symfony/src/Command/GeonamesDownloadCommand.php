<?php

namespace Chs\Message\Command;

use Chs\Message\Util\Db;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeonamesDownloadCommand extends BaseCommand {

    const GEONAMES_BASE_URL = 'https://download.geonames.org/export/dump/';
    const DESTINATION_BASE_DIR = '/tmp/data';
    const HTTP_OK = 200;

    protected static $defaultName = 'geonames:download';

    protected function configure() {
        $this
            ->setDescription('Download incremental daily files')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Check if available, do not download')
            ->setHelp(<<<TXT

Download the daily incremental files from Geonames

TXT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output);

        $dryrun = $input->getOption('dry-run');

        foreach (['modifications', 'deletes'] as $file) {
            $url = self::GEONAMES_BASE_URL . $file . '-' . date('Y-m-d', strtotime('yesterday')) . '.txt';
            $destinationFile = realpath(self::DESTINATION_BASE_DIR) . "/" . date('Y-m-d', strtotime('yesterday')) . '-' . $file . '.txt';
            $this->io->note('Processing URL: ' . $url);

            $result = $this->getHttpResponseCode_using_curl($url);
            if (self::HTTP_OK === $result) {
                $this->io->success('File was located, downloading file to :' . $destinationFile);
                if ($dryrun) {
                    $this->io->note('Dry-Run enabled, file will not be downloaded');
                } else {
                    if (file_put_contents($destinationFile, file_get_contents($url))) {
                        $this->createIncemenetalFileRecord($destinationFile);
                        $this->io->success($destinationFile . ' written');
                    } else {
                        $this->io->error('There was an error downloading or writing ' . $url);
                    }
                }
            } else {
                $this->io->error('File did not return a successful response');
            }
        }

        return self::SUCCESS;

    }

    protected function createIncemenetalFileRecord($filename){
        Db::getConnection()->executeQuery(<<<SQL
INSERT INTO incremental_files (filename, download_date) VALUE (?, NOW())
SQL
        , [$filename]);
    }

    /**
     * Only get the response header to ensure the file is available before trying to download its contents
     * source : https://stackoverflow.com/questions/2280394/how-can-i-check-if-a-url-exists-via-php
     *
     * @param string $url
     * @param bool $followredirects
     * @return false|int
     */
    function getHttpResponseCode_using_curl(string $url, bool $followredirects = true) {
        // returns int responsecode, or false (if url does not exist or connection timeout occurs)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if (!$url || !is_string($url)) {
            return false;
        }
        $ch = @curl_init($url);
        if ($ch === false) {
            return false;
        }
        @curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY, true);    // dont need body
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // catch output (do NOT print!)
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);       // do not try to validate ssl
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);       // do not try to validate ssl
        if ($followredirects) {
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            @curl_setopt($ch, CURLOPT_MAXREDIRS, 10);  // fairly random number, but could prevent unwanted endless redirects with followlocation=true
        } else {
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        }
//      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);   // fairly random number (seconds)... but could prevent waiting forever to get a result
//      @curl_setopt($ch, CURLOPT_TIMEOUT        ,6);   // fairly random number (seconds)... but could prevent waiting forever to get a result
        @curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1");   // pretend we're a regular browser
        @curl_exec($ch);
        if (@curl_errno($ch)) {   // should be 0
            @curl_close($ch);
            return false;
        }
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
        @curl_close($ch);
        return $code;
    }

}