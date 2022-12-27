<?php

namespace Chs\Geoname\Util;

use Symfony\Component\Console\Application as SymfonyApplication;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;

class Application extends SymfonyApplication{

    const NAME = 'GeoNames Console';
    const VERSION = '0.0.1';

    /**
     * @throws ReflectionException
     */
    public function __construct() {
        parent::__construct(self::NAME, self::VERSION);
        $this->registerCommands();
    }

    /**
     * Dynamically register all commands in the Command folder
     *
     * @return void
     * @throws ReflectionException
     */
    protected function registerCommands()
    {
        if (!is_dir($dir = __DIR__ . '/../Command')) return;

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = 'Chs\\Geoname\\Command';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns = $prefix . '\\'.strtr($relativePath, '/', '\\');
            }
            $r = new ReflectionClass($ns.'\\'.$file->getBasename('.php'));
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
                /** @var Command $r */
                $this->add($r->newInstance());
            }
        }
    }

}