<?php

namespace OkStuff\PhpNsq\Command;

use Closure;
use OkStuff\PhpNsq\PhpNsq;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Psr\Log\LoggerInterface;
use React\EventLoop\StreamSelectLoop;
use React\EventLoop\ExtLibeventLoop;
use React\EventLoop\ExtEventLoop;
use React\EventLoop\LoopInterface;
use React\EventLoop\ExtEvLoop;
use React\EventLoop\ExtLibevLoop;
use React\EventLoop\ExtUvLoop;

class Base extends Command
{
    protected static PhpNsq $phpnsq;
    protected static ExtUvLoop|ExtLibevLoop|ExtEvLoop|LoopInterface|ExtEventLoop|ExtLibeventLoop|StreamSelectLoop $loop;

    public function __construct(array $config = null, $name = null, ?LoggerInterface $logger = null)
    {
        parent::__construct($name);

        self::$loop = Factory::create();
        self::$phpnsq = new PhpNsq($config, $logger);
    }

    public function runLoop(): void
    {
        self::$loop->run();
    }

    public function addReadStream($socket, Closure $closure): static
    {
        self::$loop->addReadStream($socket, $closure);

        return $this;
    }

    public function addPeriodicTimer($interval, Closure $closure): static
    {
        self::$loop->addPeriodicTimer($interval, $closure);

        return $this;
    }
}
