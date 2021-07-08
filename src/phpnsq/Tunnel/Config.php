<?php

namespace OkStuff\PhpNsq\Tunnel;

use Exception;

use function count;
use function is_array;

class Config
{
    public bool $initialized = false;

    public function __construct(
        public string $host,
        public int $port
    )
    {
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

    //check if all the value is between min and max value.
    public function validate(): bool
    {
        foreach ($this as $key => $val) {
            if (is_array($val) && 3 === count($val)) {
                if (!isset($val['default']) || !isset($val['min']) || !isset($val['max'])) {
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
