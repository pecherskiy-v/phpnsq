<?php

namespace OkStuff\PhpNsq\Conn;

use Exception;

use function is_array;
use function count;

class Config
{
    public string $host;
    public int $port;

    private int $clientTimeout = 30;

    private array $readTimeout = [
        'default' => 60,
        'min' => 0.1,
        'max' => 5 * 60,
    ];
    private array $writeTimeout = [
        'default' => 1,
        'min' => 0.1,
        'max' => 5 * 60,
    ];

    private array $maxBackoffDuration = [
        'default' => 2 * 60,
        'min' => 0,
        'max' => 60 * 60,
    ];
    private array $backoffMultiplier = [
        'default' => 1,
        'min' => 0,
        'max' => 60 * 60,
    ];

    private array $maxAttempts = [
        'default' => 5,
        'min' => 0,
        'max' => 65535,
    ];

    private int $heartbeatInterval = 30;

    private bool $blocking = true;

    private bool $authSwitch = false;

    private string $authSecret = "";

    private string $logdir = "";

    public function __construct(string $host = "", int $port = 0)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function set($key, $val): static
    {
        if (is_array($this->$key)) {
            $this->$key['default'] = $val;
        } else {
            $this->$key = $val;
        }

        return $this;
    }

    public function get($key)
    {
        return $this->$key;
    }

    /**
     * @throws Exception
     */
    public function validate(): bool
    {
        foreach ($this as $key => $val) {
            if (is_array($val) && 3 === count($val)) {
                if (!isset($val['default'], $val['min'], $val['max'])) {
                    throw new Exception(sprintf("invalid %s value", $key));
                }

                if ($val['default'] < $val['min']) {
                    throw new Exception(sprintf("invalid %s ! %v(default) < %v(min)", $key, $val['default'], $val['min']));
                }

                if ($val['default'] > $val['max']) {
                    throw new Exception(sprintf("invalid %s ! %v(default) > %v(max)", $key, $val['default'], $val['max']));
                }
            }
        }

        return true;
    }
}
