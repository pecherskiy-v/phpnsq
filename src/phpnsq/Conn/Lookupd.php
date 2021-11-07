<?php
namespace OkStuff\PhpNsq\Conn;

class Lookupd
{
    public const LOOKUP_TOPIC_URI = "http://%s:%d/lookup?topic=%s";

    private Config $config;

    private bool $nsqdConnected = false;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @throws \JsonException
     */
    public function getProducers(string $topic): array
    {
        $nsqdConns = [];

        if ($this->nsqdConnected) {
            return $nsqdConns;
        }

        $defaults = [
            CURLOPT_URL => sprintf(self::LOOKUP_TOPIC_URI, $this->config->host, $this->config->port, $topic),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 4
        ];
      
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        if( ! $result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);

        $d = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        if (isset($d["message"]) && "TOPIC_NOT_FOUND" === $d["message"]) {
            return $nsqdConns;
        }

        foreach ($d["producers"] as $producer) {
            $nsqdConns[] = $this->connectProducer($producer);
        }

        $this->nsqdConnected = true;

        return $nsqdConns;
    }

    private function connectProducer($producer): Nsqd
    {
        $config = new Config(explode(':',$producer['remote_address'])[0], $producer["tcp_port"]);
        $config->set("authSwitch", $this->config->get("authSwitch"))
            ->set("authSecret", $this->config->get("authSecret"))
            ->set("logdir", $this->config->get("logdir"));
        if (!empty($this->config->get("tlsConfig"))) {
            $config->set("tlsConfig", $this->config->get("tlsConfig"));
        }
        return new Nsqd($config);
    }
}
