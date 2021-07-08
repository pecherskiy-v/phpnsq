<?php

namespace OkStuff\PhpNsq\Tunnel;

use Exception;

use function count;
use function is_array;

class Config
{
    public bool $initialized = false;

    private int $dialTimeout = 1;
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
    private string $localAddr;

    private array $lookupdPollInterval = [
        'default' => 60,
        'min' => 0.01,
        'max' => 5 * 60,
    ];
    private array $lookupdPollJitter = [
        'default' => 0.3,
        'min' => 0,
        'max' => 1,
    ];

    private array $maxRequeueDelay = [
        'default' => 15 * 60,
        'min' => 0,
        'max' => 60 * 60,
    ];
    private array $defaultRequeueDelay = [
        'default' => 90,
        'min' => 0,
        'max' => 60 * 60,
    ];

    //TODO: need to be fixed
    private mixed $backoffStrategy;
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

    private array $lowRdyIdleTimeout = [
        'default' => 10,
        'min' => 1,
        'max' => 5 * 60,
    ];
    private array $lowRdyTimeout = [
        'default' => 30,
        'min' => 1,
        'max' => 5 * 60,
    ];
    private array $rdyRedistributeInterval = [
        'default' => 5,
        'min' => 0.001,
        'max' => 5,
    ];

    public function __construct(
        public string $host,
        public int $port
    ) {
        $this->initialized = true;
    }

    public function set($key, $val): static
    {
        if (isset($this->$key)) {
            if (is_array($this->$key)) {
                $this->$key['default'] = $val;
            } else {
                $this->$key = $val;
            }
        }

        return $this;
    }

    public function get($key)
    {
        return $this->$key;
    }

    /**
     * check if all the value is between min and max value.
     * @return bool
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
