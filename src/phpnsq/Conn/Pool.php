<?php

namespace OkStuff\PhpNsq\Conn;

use function count;

class Pool
{
    private array $nsqCfg;
    private array $pool = [];
    private array $lookupdPool = [];

    public function __construct(array $nsq, bool $lookupd)
    {
        $this->nsqCfg = $nsq["nsq"];

        if ($lookupd) {
            foreach ($nsq["nsq"]["lookupd_addrs"] as $value) {
                $addr = explode(":", $value);
                $config = new Config($addr[0], $addr[1]);
                $config->set("authSwitch", $nsq["nsq"]["auth_switch"])
                       ->set("authSecret", $nsq["nsq"]["auth_secret"])
                       ->set("logdir", $nsq["nsq"]["logdir"]);
                if (!empty($nsq["nsq"]["tls_config"])) {
                    $config->set("tlsConfig", $nsq["nsq"]["tls_config"]);
                }
                $this->addLookupd(new Lookupd($config));
            }
        } else {
            $this->addNsqd();
        }
    }

    public function addLookupd(Lookupd $conn): static
    {
        $this->lookupdPool[] = $conn;

        return $this;
    }

    private function addNsqd(): void
    {
        foreach ($this->nsqCfg["nsqd_addrs"] as $value) {
            $addr = explode(":", $value);
            $config = new Config($addr[0], $addr[1]);
            $config->set("authSwitch", $this->nsqCfg["auth_switch"])
                   ->set("authSecret", $this->nsqCfg["auth_secret"])
                   ->set("logdir", $this->nsqCfg["logdir"]);
            if (!empty($this->nsqCfg["tls_config"])) {
                $config->set("tlsConfig", $this->nsqCfg["tls_config"]);
            }
            $this->addConn(new Nsqd($config));
        }
    }

    public function addConn(Nsqd ...$conns): static
    {
        foreach ($conns as $conn) {
            $this->pool[] = $conn;
        }

        return $this;
    }

    public function getConn()
    {
        return $this->pool[array_rand($this->pool)];
    }

    /**
     * @throws \JsonException
     */
    public function addNsqdByLookupd(Lookupd $conn, string $topic): static
    {
        $nsqdConns = $conn->getProducers($topic);
        if (count($nsqdConns) <= 0) {
            $this->addNsqd();
        } else {
            $this->addConn(...$nsqdConns);
        }

        return $this;
    }

    public function getLookupd()
    {
        return $this->lookupdPool[array_rand($this->lookupdPool)];
    }

    public function getLookupdCount(): int
    {
        return count($this->lookupdPool);
    }
}
