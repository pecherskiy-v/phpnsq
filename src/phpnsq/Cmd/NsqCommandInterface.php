<?php

declare(strict_types=1);

namespace OkStuff\PhpNsq\Command;

use Closure;

interface NsqCommandInterface
{
    public function runLoop(): void;

    public function addReadStream($socket, Closure $closure): static;

    public function addPeriodicTimer($interval, Closure $closure): static;
}
