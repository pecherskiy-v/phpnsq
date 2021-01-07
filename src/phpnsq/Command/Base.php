<?php

namespace OkStuff\PhpNsq\Command;

use Closure;
use OkStuff\PhpNsq\PhpNsq;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Psr\Log\LoggerInterface;

class Base extends Command
{
    protected static $phpnsq;
    protected static $loop;

    public function __construct(array $config = null, $name = null, ?LoggerInterface $logger = null)
    {
        parent::__construct($name);

        self::$loop = Factory::create();
        self::$phpnsq = new PhpNsq($config, $logger);
    }

    public function runLoop()
    {
        self::$loop->run();
    }

    public function addReadStream($socket, Closure $closure)
    {
        self::$loop->addReadStream($socket, $closure);

        return $this;
    }

    public function addPeriodicTimer($interval, Closure $closure)
    {
        self::$loop->addPeriodicTimer($interval, $closure);

        return $this;
    }
}
