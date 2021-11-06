<?php

namespace OkStuff\PhpNsq\Stream;

class Message
{
    private bool $decoded = false;
    private mixed $id;
    private mixed $body;
    private mixed $timestamp;
    private mixed $attempts;
    private mixed $nsqdAddr;
    private mixed $delegate;

    public function __construct()
    {
        $this->timestamp = microtime(true);
    }

    public function isDecoded(): bool
    {
        return $this->decoded;
    }

    public function setDecoded(): static
    {
        $this->decoded = true;

        return $this;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function setBody($body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getTimestamp(): mixed
    {
        return $this->timestamp;
    }

    public function setTimestamp(mixed $timestamp = null): static
    {
        $this->timestamp = $timestamp ?? microtime(true);
     
        return $this;
    }

    public function getAttempts(): mixed
    {
        return $this->attempts;
    }

    public function setAttempts($attempts): static
    {
        $this->attempts = $attempts;

        return $this;
    }

    public function getNsqdAddr(): mixed
    {
        return $this->nsqdAddr;
    }

    public function setNsqdAddr($nsqdAddr): static
    {
        $this->nsqdAddr = $nsqdAddr;

        return $this;
    }

    public function getDelegate(): mixed
    {
        return $this->delegate;
    }

    public function setDelegate($delegate): static
    {
        $this->delegate = $delegate;

        return $this;
    }

    public function toArray()
    {
        return [
            "id" => $this->getId(),
            "body" => $this->getBody(),
            "timestamp" => $this->getTimestamp(),
            "decoded" => $this->isDecoded(),
            "attempts" => $this->getAttempts(),
            "nsqdAddr" => $this->getNsqdAddr(),
            "delegate" => $this->getDelegate(),
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray(), JSON_FORCE_OBJECT);
    }
}
