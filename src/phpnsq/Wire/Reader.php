<?php

namespace OkStuff\PhpNsq\Wire;

use Exception;
use OkStuff\PhpNsq\Message\Message;
use OkStuff\PhpNsq\Tunnel\Tunnel;
use OkStuff\PhpNsq\Utility\IntPacker;

class Reader
{
    public const TYPE_RESPONSE = 0;

    public const TYPE_ERROR = 1;

    public const TYPE_MESSAGE = 2;

    public const HEARTBEAT = "_heartbeat_";

    public const OK = "OK";

    private ?Tunnel $tunnel;
    private array $frame;

    public function __construct(Tunnel $tunnel = null)
    {
        $this->tunnel = $tunnel;
    }

    public function bindTunnel(Tunnel $tunnel): static
    {
        $this->tunnel = $tunnel;

        return $this;
    }

    public function bindFrame(): static
    {
        $size = 0;
        $type = 0;
        try {
            $size = $this->readInt(4);
            $type = $this->readInt(4);
        } catch (Exception $e) {
            throw new Exception("Error reading message frame [$size, $type] ({$e->getMessage()})");
        }

        $frame = [
            "size" => $size,
            "type" => $type,
        ];

        try {
            if (self::TYPE_RESPONSE == $type) {
                $frame["response"] = $this->readString($size - 4);
            } elseif (self::TYPE_ERROR == $type) {
                $frame["error"] = $this->readString($size - 4);
            }
        } catch (Exception $e) {
            throw new Exception("Error reading frame details [$size, $type] ({$e->getMessage()})");
        }

        $this->frame = $frame;

        return $this;
    }

    // DecodeMessage deserializes data (as []byte) and creates a new Message
    // message format:
    //  [x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x][x]...
    //  |       (int64)        ||    ||      (hex string encoded in ASCII)           || (binary)
    //  |       8-byte         ||    ||                 16-byte                      || N-byte
    //  ------------------------------------------------------------------------------------------...
    //    nanosecond timestamp    ^^                   message ID                       message body
    //                         (uint16)
    //                          2-byte
    //                         attempts

    private function readInt($size): string
    {
        [, $tmp] = unpack("N", $this->tunnel->read($size));

        return sprintf("%u", $tmp);
    }

    private function readString($size): string
    {
        $bytes = unpack("c{$size}chars", $this->tunnel->read($size));

        return implode(array_map("chr", $bytes));
    }

    public function getMessage(): ?Message
    {
        if (null !== $this->frame && self::TYPE_MESSAGE == $this->frame["type"]) {
            return (new Message())->setTimestamp($this->readInt64(8))
                                  ->setAttempts($this->readUInt16(2))
                                  ->setId($this->readString(16))
                                  ->setBody($this->readString($this->frame["size"] - 30))
                                  ->setDecoded();
        }

        return null;
    }

    private function readInt64($size)
    {
        return IntPacker::int64($this->tunnel->read($size));
    }

    private function readUInt16($size)
    {
        return IntPacker::uInt16($this->tunnel->read($size));
    }

    public function isMessage(): bool
    {
        return self::TYPE_MESSAGE == $this->frame["type"];
    }

    public function isHeartbeat(): bool
    {
        return $this->isResponse(self::HEARTBEAT);
    }

    public function isResponse($response = null): bool
    {
        return isset($this->frame["response"])
               && self::TYPE_RESPONSE == $this->frame["type"]
               && (null === $response || $response === $this->frame["response"]);
    }

    public function isOk(): bool
    {
        return $this->isResponse(self::OK);
    }
}
