<?php

namespace OkStuff\PhpNsq\Tunnel;

use OkStuff\PhpNsq\Utility\Stream;
use OkStuff\PhpNsq\Wire\Writer;

use function strlen;

class Tunnel
{
    private Config $config;
    private $sock;
    private array $writer = [];
    private array $reader = [];

    private bool $identify = false;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function read(int $len = 0): string
    {
        $data         = '';
        $timeout      = $this->config->get("readTimeout")["default"];
        $this->reader = [$sock = $this->getSock()];
        while (strlen($data) < $len) {
            $readable = Stream::select($this->reader, $this->writer, $timeout);
            if ($readable > 0) {
                $buffer = Stream::recvFrom($sock, $len);
                $data   .= $buffer;
                $len    -= strlen($buffer);
            }
        }

        return $data;
    }

    public function write(string $buffer): static
    {
        $timeout      = $this->config->get("writeTimeout")["default"];
        $this->writer = [$sock = $this->getSock()];
        while (!empty($buffer)) {
            $writable = Stream::select($this->reader, $this->writer, $timeout);
            if ($writable > 0) {
                $buffer = substr($buffer, Stream::sendTo($sock, $buffer));
            }
        }

        return $this;
    }

    public function __destruct()
    {
        fclose($this->getSock());
    }

    public function getSock()
    {
        if (null === $this->sock) {
            $this->sock = Stream::pfopen($this->config->host, $this->config->port);

            if (false === $this->config->get("blocking")) {
                stream_set_blocking($this->sock, 0);
            }

            $this->write(Writer::MAGIC_V2);
        }

        return $this->sock;
    }

    //TODO:
    public function setIdentify(): static
    {
        if (false === $this->identify) {
            $this->write(Writer::identify());
        }

        return $this;
    }
}
