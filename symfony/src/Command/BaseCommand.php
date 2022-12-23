<?php

namespace Chs\Message\Command;

use Chs\Message\Util\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command {

    protected SymfonyStyle $io;
    protected \Monolog\Logger $log;

    public function __construct(string $name = null) {
        parent::__construct($name);
        $this->log = Logger::getLogger();
        $this->log->pushProcessor(function($entry){
            $entry['extra']['class'] = static::class;
            return $entry;
        });
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Get the base path of the epay solution
     * @return false|string
     */
    protected static function getBasePath(){
        return realpath(__DIR__ . '/../../../');
    }

    /**
     * A message or array of messages to log to the output
     *
     * @param string|array $message
     * @return void
     */
    protected function log($message){
        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->io->writeln(sprintf('[%s] %s', date('Y-m-d\TH:i:sO'), $message));
        }
    }
}